<?php namespace App\Http\Controllers\BData;
/**
 * Created by PhpStorm.
 * User: purplebleed
 * Date: 2015/9/21
 * Time: 下午 09:36
 */

use App\Buildcase;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\Pdata\ContactInfo;
use App\Model\Pdata\FoodCategory;
use App\Model\Pdata\Food;
use App\Model\Pdata\Message;
use App\Model\Pdata\UserFoodDetail;
use App\Patientprofile;
use Illuminate\Http\Request;
use App\Model\Pdata\BloodSugar;
use App\Model\Pdata\BloodSugarDetail;
use App\Model\Pdata\HospitalNo;
use App\User;
use Session;
use Auth;
use Cache;
use Input;
use DB;
use App\Feature;
use App\Caselist;


    class BDataController extends Controller{

        private $nodes = ['early_morning', 'morning', 'breakfast_before', 'breakfast_after', 'lunch_before', 'lunch_after', 'dinner_before', 'dinner_after', 'sleep_before'];

        public function __construct()
        {
            $this->middleware('auth');
        }

        public function page( $uuid = null, $end = null)
        {

            $users = Auth::user();

            if($uuid == null){
                $patient = Patientprofile::where('user_id', '=', $users->id) -> first();
                if($patient != null){
                    $hospital_no = $patient-> hospital_no;
                }else{
                    $err_msg = '请重新登入!';
                }

                if(isset($hospital_no) && $hospital_no != null){
                    $uuid = $hospital_no -> hospital_no_uuid;
                }else{
                    $err_msg = '没有血糖资料!';
                }
            }else{

                $hospital_no = null;
                $hospital_no = HospitalNo::find($uuid);

                if($hospital_no != null){
                    $patient = $hospital_no -> patient;
//                    if($hospital_no -> patient_user_id != $users -> id){
//                        $user_feature = Feature::where('href', '=', '/patient') -> first() -> hasfeatures() -> where('user_id', '=', $users -> id)->first();
//                        if($user_feature == null){
//                            $hospital_no = null;
//                        }
//                    }
                }
                if($hospital_no == null){
                    $err_msg = '您没有权限查看此血糖资料!';
                }
            }

            if(!isset($hospital_no) || $hospital_no == null){
                return view('bdata.error', compact('err_msg'));
            }

            if($end > date('Y-m-d')){
                return redirect('/bdata/'.$uuid);
            }

            if($end == null){
                $data['next'] = null;
                $end = date('Y-m-d');
            }else{
                $next = date('Y-m-d',strtotime("1 month", strtotime($end)));
                if($end == date('Y-m-d')){
                    $data['next'] = null;
                }
                else if($next == date('Y-m-d')){
                    $data['next'] = "/bdata/".$uuid;
                }else{
                    $data['next'] = "/bdata/".$uuid."/".$next;
                }
            }

            $start = date('Y-m-d', strtotime("-1 month", strtotime($end)));
            $data['previous'] = "/bdata/".$uuid."/".$start;

            if($hospital_no->count() ==0 ) {
                $invalid = true;
                return view('bdata.bdata', compact('invalid'));
            }

            $data['displayname'] = $hospital_no->hospital_no_displayname;

            $data['patient_displayname'] = User::find($hospital_no->patient_user_id)->name;
            $data['patient_bday'] =  $patient -> pp_birthday;
            $data['patient_age'] =  $patient -> pp_age;
            $data['patient_id'] =  $patient -> pp_patientid;

            $blood_records = $hospital_no->blood_sugar()->where('calendar_date', '<=', $end)->where('calendar_date', '>', $start)->orderBy('calendar_date', 'DESC')->get();

            $stat = $this -> get_stat($blood_records);
            $notes = $this -> get_notes($blood_records);

            $blood_records = $this->fillup($blood_records,$start,$end);

            $data['food_categories'] = FoodCategory::all();

            $food_records = $this->get_has_food($uuid);

            $soap_link = "";
//            if(isset($user_feature) && $user_feature != null){
                $soap_link = '/soap/'.$uuid ;
//            }

            $contact_data = $hospital_no -> contact_info;
            if($contact_data != null){
                $duty = Buildcase::where('hospital_no_uuid', '=', $uuid)->orderBy('duty_at','desc')->first();
                if($duty == null){
                    $duty = $hospital_no->nurse_user_id;
                }else{
                    $duty = $duty->duty;
                }
                $contact_data["nurse_name"] = User::find($duty)->name;
                $contact_data["trace_method"] = $this->convert_trace_method($contact_data["trace_method"]);
                $contact_data["contact_time"] = $this->convert_contact_time($contact_data["contact_time"]);
                $contact_data["phone"] = User::find($hospital_no->patient_user_id)->phone;
                $contact_data["email"] = User::find($hospital_no->patient_user_id)->email;
            }
            $goal = $hospital_no -> hba1c_goal;
            $goal_matrix = $hospital_no -> hba1c_goal_matrix();

            Session::put('uuid', $uuid);

            return view('bdata.bdata', compact('blood_records', 'data', 'food_categories', 'stat', 'food_records', 'soap_link', 'notes', 'contact_data', 'goal', 'goal_matrix'));
        }

        private function get_has_food($uuid){
            $calendar_date = date('Y-m-d');
            $start = date('Y-m-d', strtotime("-1 month", strtotime($calendar_date)));
            $records = HospitalNo::find($uuid)->food_record()->where('calendar_date','<=',$calendar_date)-> where('calendar_date','>',$start)->get();

            $food_all_calendar = array();
            foreach($records as $record){
                if(isset($food_all_calendar[$record['calendar_date']])){
                    array_push($food_all_calendar, $record['measure_type']);
                }else{
                    $food_all_calendar[$record['calendar_date']] = [$record['measure_type']];
                }
            }

            return $food_all_calendar;
        }

        private function convert_trace_method($trace_method){
            $result = "";
            switch($trace_method){
                case 1:
                    $result = "电话";
                    break;
                case 2:
                    $result = "传真";
                    break;
                case 3:
                    $result = "e-mail";
                    break;
                case 4:
                    $result = "回诊讨论";
                    break;
                case 5:
                    $result = "网路平台";
                    break;
                case 6:
                    $result = "传输线";
                    break;
                case 7:
                    $result = "其他";
                    break;
            }
            return $result;
        }

        private function convert_contact_time($contact_time){
            $result = "";
            switch($contact_time){
                case 1:
                    $result = "早上";
                    break;
                case 2:
                    $result = "下午";
                    break;
                case 3:
                    $result = "晚上";
                    break;
                case 4:
                    $result = "全天";
                    break;
                case 5:
                    $result = "其他";
                    break;
            }
            return $result;
        }

        private function get_notes($blood_sugars){
            $notes = array();
            foreach($blood_sugars as $blood_sugar){
                $details = $blood_sugar -> blood_sugar_detail;
                foreach($details as $detail){
                    if($detail -> note !== null){
                        $notes[$blood_sugar -> calendar_date][$detail -> measure_type] = str_replace(["\r\n", "\r", "\n"], "<br/>", $detail -> note);
                    }
                }
            }

            return $notes;
        }

        public function get_filter(Request $request){
            $pks = explode(" ",$request['filter']);
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);

            $blood_records = array();
            if($hospital_no != null){
                $blood_sugars = $hospital_no -> blood_sugar;
                foreach($blood_sugars as $blood_sugar){
                    if(in_array($blood_sugar->blood_sugar_pk, $pks)){
                        array_push($blood_records, $blood_sugar);
                    }
                }
            }

            return view('bdata.filter_data', compact('blood_records'));
        }

        public function upsert_note(Request $request){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $calendar_date = $request['calendar_date'];
            $note = $request['day_note'];

            if($hospital_no != null){
                $blood_sugar = $hospital_no -> blood_sugar() -> firstOrNew(array('calendar_date' => $calendar_date));
                if($blood_sugar !== null){
                    DB::beginTransaction();
                    try {
                        $blood_sugar -> user_id = $hospital_no-> patient_user_id;
                        $blood_sugar -> note = $note;
                        $blood_sugar->save();
                        DB::commit();
                        return "success";
                    }catch (\Exception $e){

                        DB::rollback();
                        return "fail";
                    }
                }
            }

            return "fail";
        }

        public function get_all_stat(){

            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $user_id = Auth::user() -> id;

            $food_records = $this -> get_food_stat($uuid);

            if($hospital_no -> patient_user_id == $user_id){
                return view('bdata.food_statics', compact('food_records'));
            //}else if($hospital_no -> nurse_user_id == $user_id){
            }else{
                $blood_records = $this -> get_blood_stat($uuid);
                return view('bdata.blood_statics', compact('food_records', 'blood_records'));
            }
        }

        public function get_blood_chart(){
            $uuid = Session::get('uuid');
            $blood_records = $this -> get_blood_stat($uuid);
            return $blood_records;
        }

        public function get_hba1c(){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $avg = array();
            $avg['name'] = User::find($hospital_no->patient_user_id) -> name;
            if($hospital_no != null){
                $ranges = [date('Y-m-d')];
                $profile_id = $hospital_no -> patient_profile_id;
                $tmp_data = Caselist::where('pp_id', '=' ,$profile_id) -> whereNotNull('cl_blood_hba1c')->orderBy('created_at', 'desc')->take(4)->get();
                $avg['data'] = $tmp_data;
                //temp work around for last data
                /*for($i = 1; $i <= 4; $i++){
                    array_push($ranges,date('Y-m-d', strtotime("-3 month", strtotime($ranges[$i - 1]))));
                    $avg[$ranges[$i]]["avg"] =  Caselist::where('pp_id', '=' ,$profile_id) -> where('created_at','<', $ranges[$i - 1]) -> where('created_at','>', $ranges[$i]) ->avg('cl_blood_hba1c');
                    $avg[$ranges[$i]]["last_date"] =  Caselist::where('pp_id', '=' ,$profile_id) -> where('created_at','<', $ranges[$i - 1]) -> where('created_at','>', $ranges[$i]) -> max('created_at');
                    $avg[$ranges[$i]]["first_date"] =  Caselist::where('pp_id', '=' ,$profile_id) -> where('created_at','<', $ranges[$i - 1]) -> where('created_at','>', $ranges[$i]) -> min('created_at');
                    $avg[$ranges[$i]]["count"] =  Caselist::where('pp_id', '=' ,$profile_id) -> where('created_at','<', $ranges[$i - 1]) -> where('created_at','>', $ranges[$i]) -> count('cl_blood_hba1c');
                }*/
            }

            return $avg;
        }

        private function get_blood_stat($uuid){
            $calendar_date = date('Y-m-d');
            $start = date('Y-m-d', strtotime("-2 month", strtotime($calendar_date)));

            $goal_up = array();
            $goal_low = array();

            $types = ["breakfast", "lunch", "dinner"];
            $goals = ["above", "normal", "below", "all"];
            $bb_goals = ["above_p", "above_m", "normal_p", "normal_m"];
            $data_types = ["count", "avg"];

            foreach($types as $type){
                foreach($goals as $goal){
                    foreach($data_types as $data_type){
                        $pc_arr[$type][$goal][$data_type] = 0;
                    }
                    $pc_arr[$type][$goal]["filter_str"] = "";
                }

                foreach($bb_goals as $bb_goal){
                    foreach($data_types as $data_type) {
                        $bb_arr[$type][$bb_goal][$data_type] = 0;
                    }
                    $bb_arr[$type][$bb_goal]["filter_str"] = "";
                }
            }

            $hospital_no = HospitalNo::find($uuid);
            $hba1c_goal_matrix = $hospital_no -> hba1c_goal_matrix();
            $records = $hospital_no->blood_sugar()->where('calendar_date','<=',$calendar_date)-> where('calendar_date','>',$start)->orderby('calendar_date')->get();
            $blood_tmp = array();

            for($i = 0; $i < count($records); $i++){
                $record = $records[$i];

                //normal statistics
                foreach( $this -> nodes as $node){
                    if($record[$node] != null) {
                        if(isset($blood_tmp[$node]) && $record[$node] ){
                            array_push($blood_tmp[$node],$record[$node]);
                        }else{
                            $blood_tmp[$node] = [$record[$node]];
                        }
                    }

                    if($node == 'breakfast_before' || $node == 'lunch_before' || $node == 'dinner_before' || $node == 'early_morning' || $node == 'morning'){
                        $goal_up[$node] = $hba1c_goal_matrix -> goal_before_meal_high;
                        $goal_low[$node] = $hba1c_goal_matrix -> goal_before_meal_low;
                    }
                    else if($node == 'breakfast_after' || $node == 'lunch_after' || $node == 'dinner_after'){
                        $goal_up[$node] = $hba1c_goal_matrix -> goal_after_meal_high;
                        $goal_low[$node] = $hba1c_goal_matrix -> goal_before_meal_low;
                    }elseif($node == 'sleep_before'){
                        $goal_up[$node] = $hba1c_goal_matrix -> goal_sleep_high;
                        $goal_low[$node] = $hba1c_goal_matrix -> goal_sleep_low;
                    }
                    else{
                        $goal_up[$node] = $hba1c_goal_matrix -> goal_morning_high;
                        $goal_low[$node] = $hba1c_goal_matrix -> goal_morning_low;
                    }
                }

                //PC-AC
                if($record -> breakfast_after !== null && $record -> breakfast_before !== null){
                    $pc_breakfast = $record -> breakfast_after - $record -> breakfast_before;

                    $target = "normal";
                    if($pc_breakfast >= 3.4){
                        $target = "above";
                    }elseif( $pc_breakfast < 1.7 ) {
                        $target = "below";
                    }

                    $pc_arr["breakfast"][$target]["count"] ++;
                    $pc_arr["breakfast"][$target]["avg"] += $pc_breakfast;
                    $pc_arr["breakfast"][$target]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");

                    $pc_arr["breakfast"]["all"]["count"] ++;
                    $pc_arr["breakfast"]["all"]["avg"] += $pc_breakfast;
                }

                if($record -> lunch_after !== null && $record -> lunch_before !== null) {
                    $pc_lunch = $record -> lunch_after - $record -> lunch_before;
                    $target = "normal";
                    if($pc_lunch >= 3.4){
                        $target = "above";
                    }elseif( $pc_lunch < 1.7 ) {
                        $target = "below";
                    }
                    $pc_arr["lunch"][$target]["count"]++;
                    $pc_arr["lunch"][$target]["avg"] += $pc_lunch;
                    $pc_arr["lunch"][$target]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");

                    $pc_arr["lunch"]["all"]["count"]++;
                    $pc_arr["lunch"]["all"]["avg"] += $pc_lunch;
                }

                if($record -> dinner_after !== null && $record -> dinner_before !== null) {
                    $pc_dinner = $record -> dinner_after - $record -> dinner_before. ' '.$record -> blood_sugar_pk;
                    $target = "normal";
                    if($pc_dinner >= 3.4){
                        $target = "above";
                    }elseif( $pc_dinner < 1.7 ) {
                        $target = "below";
                    }
                    $pc_arr["dinner"][$target]["count"]++;
                    $pc_arr["dinner"][$target]["avg"] += $pc_dinner;
                    $pc_arr["dinner"][$target]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");

                    $pc_arr["dinner"]["all"]["count"]++;
                    $pc_arr["dinner"]["all"]["avg"] += $pc_dinner;
                }

                //B-B
                if($i > 0 && $record -> breakfast_before !== null && $records[$i-1] -> dinner_before !== null && $records[$i-1] -> calendar_date == date('Y-m-d', strtotime('-1 day', strtotime($record -> calendar_date)))){
                    $bb = $record -> breakfast_before - $records[$i-1] -> dinner_before;
                    if($bb >= 1.7 || $bb <= -1.7){
                        if($bb >= 0){
                            $bb_arr["breakfast"]["above_p"]["count"] ++;
                            $bb_arr["breakfast"]["above_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            $bb_arr["breakfast"]["above_p"]["filter_str"] .= ((string)$records[$i-1] -> blood_sugar_pk." ");
                        }

                        else{
                            $bb_arr["breakfast"]["above_m"]["count"] ++;
                            $bb_arr["breakfast"]["above_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            $bb_arr["breakfast"]["above_m"]["filter_str"] .= ((string)$records[$i-1] -> blood_sugar_pk." ");
                        }
                        $bb_arr["breakfast"]["above_p"]["avg"] += $bb;
                    }else{
                        if($bb >= 0){
                            $bb_arr["breakfast"]["normal_p"]["count"] ++;
                            $bb_arr["breakfast"]["normal_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            $bb_arr["breakfast"]["normal_p"]["filter_str"] .= ((string)$records[$i-1] -> blood_sugar_pk." ");
                        }
                        else{
                            $bb_arr["breakfast"]["normal_m"]["count"] ++;
                            $bb_arr["breakfast"]["normal_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            $bb_arr["breakfast"]["normal_m"]["filter_str"] .= ((string)$records[$i-1] -> blood_sugar_pk." ");
                        }
                        $bb_arr["breakfast"]["normal_p"]["avg"] += $bb;
                    }
                }
                if($record -> lunch_before !== null && $record -> breakfast_before !== null){
                    $bb = $record -> lunch_before - $record -> breakfast_before;
                    if($bb >= 1.7 || $bb <= -1.7){
                        if($bb >= 0)
                            if($bb >= 0){
                                $bb_arr["lunch"]["above_p"]["count"] ++;
                                $bb_arr["lunch"]["above_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            }

                            else{
                                $bb_arr["lunch"]["above_m"]["count"] ++;
                                $bb_arr["lunch"]["above_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                            }
                        $bb_arr["lunch"]["above_p"]["avg"] += $bb;
                    }else{
                        if($bb >= 0){
                            $bb_arr["lunch"]["normal_p"]["count"] ++;
                            $bb_arr["lunch"]["normal_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }
                        else{
                            $bb_arr["lunch"]["normal_m"]["count"] ++;
                            $bb_arr["lunch"]["normal_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }
                        $bb_arr["lunch"]["normal_p"]["avg"] += $bb;
                    }
                }
                if($record -> dinner_before !== null && $record -> lunch_before !== null){
                    $bb = $record -> dinner_before - $record -> lunch_before;
                    if($bb >= 1.7 || $bb <= -1.7){
                        if($bb >= 0){
                            $bb_arr["dinner"]["above_p"]["count"] ++;
                            $bb_arr["dinner"]["above_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }

                        else{
                            $bb_arr["dinner"]["above_m"]["count"] ++;
                            $bb_arr["dinner"]["above_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }
                        $bb_arr["dinner"]["above_p"]["avg"] += $bb;
                    }else{
                        if($bb >= 0){
                            $bb_arr["dinner"]["normal_p"]["count"] ++;
                            $bb_arr["dinner"]["normal_p"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }
                        else{
                            $bb_arr["dinner"]["normal_m"]["count"] ++;
                            $bb_arr["dinner"]["normal_m"]["filter_str"] .= ((string)$record -> blood_sugar_pk." ");
                        }
                        $bb_arr["dinner"]["normal_p"]["avg"] += $bb;
                    }
                }
            }

            foreach($types as $type){
                foreach($bb_goals as $target){
                    if($bb_arr[$type][$target]["count"] != 0){
                        $bb_arr[$type][$target]["avg"] = round($bb_arr[$type][$target]["avg"] / $bb_arr[$type][$target]["count"]);
                        $bb_arr[$type][$target]["filter_str"] = trim($bb_arr[$type][$target]["filter_str"]);
                    }
                }
            }

            foreach($types as $type){
                foreach($goals as $target){
                    if($pc_arr[$type][$target]["count"] != 0){
                        $pc_arr[$type][$target]["avg"] = round($pc_arr[$type][$target]["avg"] / $pc_arr[$type][$target]["count"]);
                        $pc_arr[$type][$target]["filter_str"] = trim($pc_arr[$type][$target]["filter_str"]);
                    }
                }
            }

            $blood_stat = array();
            foreach($blood_tmp as $key => $blood_array){
                $blood_stat[$key]["count"] = count($blood_array);
                $blood_stat[$key]["average"] = round($this->average($blood_array));
                $blood_stat[$key]["max"] = max($blood_array);
                $blood_stat[$key]["min"] = min($blood_array);

                $stat = $this -> count_w_condition($blood_array, $goal_up[$key], $goal_low[$key]);
                $blood_stat[$key]["above"] = $stat['above']." (".round($stat['above'] * 100 / $blood_stat[$key]["count"])."%)";
                $blood_stat[$key]["normal"] = $stat['normal']." (".round($stat['normal'] * 100 / $blood_stat[$key]["count"])."%)";
                $blood_stat[$key]["below"] = $stat['below']." (".round($stat['below'] * 100 / $blood_stat[$key]["count"])."%)";

            }

            $blood_stat["pc"] = $pc_arr;
            $blood_stat["bb"] = $bb_arr;

            $blood_stat["start"] = $start;
            $blood_stat["end"] = $calendar_date;
            return $blood_stat;
        }

        public function get_query_start_end($start,$end){

            if($start === null || $end === null){
                return "日期格式错误";
            }

            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $blood_records = $hospital_no->blood_sugar()->where('calendar_date', '<=', $end)->where('calendar_date', '>=', $start)->orderBy('calendar_date', 'DESC')->get();

            for($i = 0; $i < count($blood_records); $i++){
                $bsugar = $blood_records[$i];
                if($bsugar->early_morning === null &&
                    $bsugar->morning === null &&
                $bsugar->breakfast_before === null &&
                $bsugar->breakfast_after === null &&
                $bsugar->lunch_before === null &&
                $bsugar->lunch_after === null &&
                $bsugar->dinner_before === null &&
                $bsugar->dinner_after === null &&
                $bsugar->sleep_before === null ){
                    unset($blood_records[$i]);
                }
            }

            return view('bdata.batchtemplete', compact('blood_records'));
        }

        public function delete_batch(Request $request){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $start = $request -> start;
            $end = $request -> end;

            if($start === null || $end === null){
                return "日期格式错误";
            }

            DB::beginTransaction();
            try {
                $blood_records = $hospital_no->blood_sugar()->where('calendar_date', '<=', $end)->where('calendar_date', '>=', $start)->orderBy('calendar_date', 'DESC')->get();
                foreach($blood_records as $bsugar){
                    $bsugar->early_morning = null;
                    $bsugar->morning = null;
                    $bsugar->breakfast_before = null;
                    $bsugar->breakfast_after = null;
                    $bsugar->lunch_before = null;
                    $bsugar->lunch_after = null;
                    $bsugar->dinner_before = null;
                    $bsugar->dinner_after = null;
                    $bsugar->sleep_before = null;
                    $bsugar->note = null;
                    $bsugar -> save();
                    $details = $bsugar-> blood_sugar_detail();
                    foreach($details as $detail){
                        $detail -> delete();
                    }
                }

                $foods = $hospital_no-> food_record()->where('calendar_date', '<=', $end)->where('calendar_date', '>=', $start)->orderBy('calendar_date', 'DESC')->get();
                foreach($foods as $food){
                    $details = $food-> food_detail();
                    foreach($details as $detail){
                        $detail -> delete();
                    }
                    $food -> delete();
                }

                DB::commit();
                return "success";
            }catch (\Exception $e){
                DB::rollback();
                return "fail";
            }
        }

        private function get_food_stat($uuid){

            $calendar_date = date('Y-m-d');
            $start = date('Y-m-d', strtotime("-2 month", strtotime($calendar_date)));

            $goal_up = 20;
            $goal_low = 10;

            $records = HospitalNo::find($uuid)->food_record()->where('calendar_date','<=',$calendar_date)-> where('calendar_date','>',$start)->get();

            $food_tmp = array();

            foreach($records as $record){
                if(isset($food_tmp[$record['measure_type']])){
                    array_push($food_tmp[$record['measure_type']],$record['sugar_amount']);
                }else{
                    $food_tmp[$record['measure_type']] = [$record['sugar_amount']];
                }
            }

            $food_stat = array();
            foreach($food_tmp as $key => $food_array){
                $food_stat[$key]["count"] = count($food_array);
                $food_stat[$key]["average"] = round($this->average($food_array));
                $food_stat[$key]["max"] = max($food_array);
                $food_stat[$key]["min"] = min($food_array);

                $stat = $this -> count_w_condition($food_array, $goal_up, $goal_low);
                $food_stat[$key]["above"] = $stat['above']." (".round($stat['above'] * 100 / $food_stat[$key]["count"])."%)";
                $food_stat[$key]["normal"] = $stat['normal']." (".round($stat['normal'] * 100 / $food_stat[$key]["count"])."%)";
                $food_stat[$key]["below"] = $stat['below']." (".round($stat['below'] * 100 / $food_stat[$key]["count"])."%)";
            }

            $food_stat["start"] = $start;
            $food_stat["end"] = $calendar_date;

            return $food_stat;
        }

        private function count_w_condition($arr, $up, $low){

            $stat["above"] = 0;
            $stat["normal"] = 0;
            $stat["below"] = 0;

            foreach($arr as $ar){
                if($ar > $up){
                    $stat["above"] ++;
                }
                if($ar <= $up && $ar >= $low ){
                    $stat["normal"] ++;
                }
                if($low > $ar){
                    $stat["below"] ++;
                }
            }

            return $stat;
        }

        private function get_stat($blood_records){

            $stat['avg'] = array();
            $stat['deviation'] = array();
            $counter = 0;
            foreach($this -> nodes as $node){
                $tmp_arr = [];
                foreach($blood_records as $blood_record){
                    if($blood_record[$node] !== null){
                        $counter ++;
                    }
                    if($blood_record[$node] !== null)
                        array_push($tmp_arr,$blood_record[$node]);
                }

                if(count($blood_records) != 0){
                    $stat['avg'][$node] = ($this -> average($tmp_arr));
                    $stat['deviation'][$node] = round($this -> deviation($tmp_arr, $stat['avg'][$node]));
                    $stat['avg'][$node] = round($stat['avg'][$node]);
                }
                else{
                    $stat['deviation'][$node] = 0.0;
                    $stat['avg'][$node] = 0.0;
                }
            }
            $stat['total'] = $counter;
            return $stat;
        }

        private function average($arr){
            if(count($arr) == 0)
                return 0.0;
            else{
                return array_sum($arr)/count($arr);
            }
        }

        private function deviation($arr,$avg){
            if(count($arr) <= 1){
                return 0.0;
            }
            $sqr = 0.0;
            foreach($arr as $ar){
                $sqr += (($ar - $avg) * ($ar - $avg));
            }

            $sqr /= (count($arr)-1);

            return sqrt($sqr);
        }

        public function get_detail( $calendar_date, $measure_type){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $blood_records = $hospital_no->blood_sugar()->where('calendar_date', '<=', $calendar_date)->get();

            if($blood_records != null){
                foreach($blood_records as $blood_record){
                    if($blood_record->calendar_date == $calendar_date){
                        $detail = $blood_record->blood_sugar_detail()->where('measure_type' , "=", $measure_type)->first();
                        return $detail;
                    }
                }
            }
            return null;
        }

        private function fillup($data, $start, $end){
            $index = 0;
            $filled_date = array();
            $current = $end;

            for($current = $end; $current != $start; $current = date('Y-m-d', strtotime('-1 day', strtotime($current)))){

                if($index<count($data) && $data[$index]->calendar_date == $current){
                    array_push($filled_date,$data[$index]);
                    $index ++;
                }else{
                    $bsugar = new BloodSugar();
                    $bsugar->calendar_date = $current;
                    $bsugar->early_morning = null;
                    $bsugar->morning = null;
                    $bsugar->breakfast_before = null;
                    $bsugar->breakfast_after = null;
                    $bsugar->lunch_before = null;
                    $bsugar->lunch_after = null;
                    $bsugar->dinner_before = null;
                    $bsugar->dinner_after = null;
                    $bsugar->sleep_before = null;
                    $bsugar->note = null;
                    $bsugar->hospital_no_uuid = '';
                    array_push($filled_date,$bsugar);
                }
            }
            return $filled_date;
        }

        public function post_contact_trace(Request $request){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $contact_info = $hospital_no -> contact_info;
            if($hospital_no == null){
                return "fail";
            }
            if(isset($request['trace_time'])){
                DB::beginTransaction();
                try {
                    $contact_info -> trace_time = $request -> trace_time;
                    $contact_info -> save();
                    DB::commit();
                    return "success";
                }catch (\Exception $e){
                    DB::rollback();
                    return $e;
                }
            }

        }

        public function post_hba1c_goal(Request $request) {
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            if($hospital_no == null && !is_numeric($request->hba1c_goal)){
                return "fail";
            }
            DB::beginTransaction();
            try {
                $hospital_no->hba1c_goal = $request->hba1c_goal;
                $hospital_no->save();

                DB::commit();
                return "success";
            }catch (\Exception $e){
                DB::rollback();
                return $e;
            }
        }

        public function post_contact(Request $request){
            $this->validate($request, ContactInfo::rules());
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);

            if($hospital_no == null){
                return "fail";
            }

            $contact_info = $hospital_no -> contact_info;

            if($contact_info == null){
                $contact_info = new ContactInfo();
            }

            DB::beginTransaction();
            try {
                $contact_info->start_date = $request->start_date;
                $contact_info->med_date = $request->med_date;
                $contact_info->trace_method = $request->trace_method;
                $contact_info->contact_name = $request->contact_name;
                $contact_info->contact_description = $request->contact_description;
                $contact_info->medicine = $request->medicine;
                $contact_info->contact_phone = $request->contact_phone;
                $contact_info->contact_email = $request->contact_email;
                $contact_info->contact_time = $request->contact_time;
                $contact_info->patient_note = $request->patient_note;
                $contact_info->hospital_no_uuid = $uuid;

                $contact_info->save();
                DB::commit();
                return "success";
            }catch (\Exception $e){
                DB::rollback();
                return $e;
            }
        }

        public function upsert(Request $request){
            $this->validate($request, BloodSugarDetail::rules());
            if(!isset($request->blood_sugar) && $request->blood_sugar == null){
                return "fail";
            }
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);

            DB::beginTransaction();
            try{
                $blood_sugar = $hospital_no->blood_sugar()->firstOrNew(array('calendar_date' => $request->calendar_date));
                $blood_sugar -> calendar_date = $request -> calendar_date;
                $blood_sugar[$request->measure_type] = $request -> blood_sugar;
                $blood_sugar -> user_id = $hospital_no-> patient_user_id;
                $blood_sugar -> save();

                $blood_sugar_detail = $blood_sugar -> blood_sugar_detail() -> where('measure_type', '=', $request -> measure_type) -> first();
                if($blood_sugar_detail == null){
                    $blood_sugar_detail = new BloodSugarDetail();
                }
                $blood_sugar_detail -> measure_time = date('Y-m-d H:i', strtotime($request -> measure_time)) ;
                $blood_sugar_detail -> measure_type = $request -> measure_type ;
                $blood_sugar_detail -> exercise_type = $request -> exercise_type ;
                $blood_sugar_detail -> exercise_duration = $request -> exercis_duration ;
                $blood_sugar_detail -> insulin_type_1 = $request -> insulin_type_1 ;
                $blood_sugar_detail -> insulin_value_1 = $request -> insulin_value_1 ;
                $blood_sugar_detail -> insulin_type_2 = $request -> insulin_type_2 ;
                $blood_sugar_detail -> insulin_value_2 = $request -> insulin_value_2 ;
                $blood_sugar_detail -> insulin_type_3 = $request -> insulin_type_3 ;
                $blood_sugar_detail -> insulin_value_3 = $request -> insulin_value_3 ;
                $blood_sugar_detail -> sugar = $request -> sugar ;
                $blood_sugar_detail -> note = $request -> note ;
                $blood_sugar_detail -> low_blood_sugar = $request -> low_blood_sugar ;
                $blood_sugar_detail -> blood_sugar_pk = $blood_sugar -> blood_sugar_pk;
                $blood_sugar_detail -> save();

                DB::commit();
                return "success";
            }catch (\Exception $e){

                DB::rollback();

                return $e;
            }

        }

        public function upsertfood(Request $request){
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);

            DB::beginTransaction();
            try{
                $food_record = $hospital_no->food_record()->firstOrNew(array('calendar_date' => $request->calendar_date));
                $food_record -> calendar_date = $request -> calendar_date;
                $food_record -> measure_type= $request -> type;
                $food_record -> user_id = $hospital_no -> patient_user_id;
                $food_record -> sugar_amount = $request -> sugar_amount;
                $food_record -> food_note = $request -> food_note;
                $food_record -> note = $request -> overall_note;
                $food_record -> food_time = date('Y-m-d H:i', strtotime($request -> food_time));
                $food_record -> save();

                //delete old
                $food_record -> food_detail() -> delete();

                if($request -> details !== null){
                    //rule check
                    foreach($request -> details as $detail){
                        $user_food_detail = new UserFoodDetail();
                        $user_food_detail -> food_pk = $detail['food_type_option'];
                        if($detail['food_unit'] == "gram")
                            $user_food_detail -> amount_gram = $detail['amount'];
                        elseif($detail['food_unit'] == "set"){
                            $user_food_detail -> amount_set = $detail['amount'];
                        }
                        $user_food_detail -> food_category_pk = $detail['food_category'];
                        $user_food_detail -> user_food_pk = $food_record -> user_food_pk;
                        $user_food_detail -> save();
                    }
                }
                DB::commit();
                return "success";
            }catch (\Exception $e){

                DB::rollback();
                return "fail";
            }
        }

        public function get_food_detail( $calendar_date, $measure_type){
            $user_food = array();
            $uuid = Session::get('uuid');

            $user_food['summary'] = HospitalNo::find($uuid)->food_record()->where('calendar_date','=',$calendar_date)->where('measure_type','=',$measure_type)->get()->first();
            if($user_food['summary'] !== null){
                $user_food['detail'] = $user_food['summary']->food_detail;
                if($user_food['detail'] !== null){
                    foreach($user_food['detail'] as $detail){
                        $food = Food::find($detail -> food_pk);
                        $detail -> food_name = $food -> food_name;
                        $detail -> food_category_name = FoodCategory::find($detail -> food_category_pk) -> food_category_name;
                        if($detail -> amount_gram !== null){
                            $detail -> sugar = $food -> gram_sugar_value * $detail -> amount_gram ;
                        }
                        else if($detail -> amount_set !== null){
                            $detail -> sugar = $food -> set_sugar_value * $detail -> amount_set;
                        }
                    }
                }
            }

            return $user_food;
        }

        public function get_food_category($food_category_id){
            $all_food = array();//Cache::get('foods');

            if(isset($all_food)){
                if(!isset($all_food[$food_category_id])){
                    $all_food[$food_category_id] = Food::where('food_category_pk','=',$food_category_id)->get();
                }
            }else{
                $all_food = array();
                $all_food[$food_category_id] = Food::where('food_category_pk','=',$food_category_id)->get();
            }
            Cache::forever('foods',$all_food);
            $foods = $all_food[$food_category_id];

            return $foods;
        }

        public function message(){
            $uuid = Session::get('uuid');

            $hospital_no = HospitalNo::find($uuid);

            $user = array();
            $patient = $hospital_no -> patient;
            $user[$patient -> user_id] = User::find($hospital_no -> patient_user_id) -> name;
            $user[$hospital_no-> nurse_user_id] =  User::find($hospital_no-> nurse_user_id) -> name ;

            $start = Input::get('start');
            if($start !== null && is_numeric($start)){
                $messages = $hospital_no->messages()-> orderBy('created_at','desc')-> skip($start) -> take(20)->get();
            }else{
                $messages = $hospital_no->messages()-> orderBy('created_at','desc') ->take(20)->get();
            }

            foreach($messages as $message){
                $message -> sender_id = User::find($message -> sender_id) -> name;
            }

            return view('bdata.messagetemplate', compact('messages', 'user'));
        }

        public function post_message(Request $request){
            $uuid = Session::get('uuid');
            $user_id = Auth::user()->id;
            DB::beginTransaction();
            try{
                $message = new Message();
                $message -> hospital_no_uuid = $uuid;
                $message -> sender_id = $user_id;
                $message -> message = $request->message_body;
                $message -> save();

                DB::commit();
                return "success";
            }catch (\Exception $e){

                DB::rollback();
                return "fail";
            }

        }

        public function batch_update(Request $request){
            $sugar_data = $request['sugar_data'];
            $uuid = Session::get('uuid');
            $hospital_no = HospitalNo::find($uuid);
            $user_id = $hospital_no->patient_user_id;

            DB::beginTransaction();
            try{
                foreach($sugar_data as $one_data){
                    $calendar_date = $one_data['calendar_date'];
                    if(count(array_keys($one_data)) == 1){
                        $blood_sugar_data = $hospital_no->blood_sugar()->where('calendar_date', '=' , $calendar_date) ->first();
                        if($blood_sugar_data !=null){
                            $details = $blood_sugar_data -> blood_sugar_detail;
                            foreach( $details as $detail){
                                $detail -> delete();
                            }
                            $blood_sugar_data -> early_morning = null;
                            $blood_sugar_data -> morning = null;
                            $blood_sugar_data -> breakfast_before = null;
                            $blood_sugar_data -> breakfast_after = null;
                            $blood_sugar_data -> lunch_before = null;
                            $blood_sugar_data -> lunch_after = null;
                            $blood_sugar_data -> dinner_before = null;
                            $blood_sugar_data -> dinner_after = null;
                            $blood_sugar_data -> sleep_before = null;
                            $blood_sugar_data -> save();
                        }
                    }
                    else{
                        $blood_sugar = HospitalNo::find($uuid)->blood_sugar()->firstOrNew(array('calendar_date' => $calendar_date));

                        foreach( $one_data as $key => $value){
                            $blood_sugar[$key] = $value;
                        }
                        $blood_sugar -> calendar_date = $calendar_date;
                        $blood_sugar -> user_id = $user_id;
                        $blood_sugar -> save();

                    }
                }

                DB::commit();
                return "success";
            }catch (\Exception $e){

                DB::rollback();
                return "fail";
            }
        }

        public function delete_food($calendar_date){

            $uuid = Session::get('uuid');
            $user_food = HospitalNo::find($uuid) -> food_record() -> where('calendar_date', '=', $calendar_date) -> first();

            DB::beginTransaction();
            try{
                if($user_food != null){
                    $details = $user_food -> food_detail;
                    foreach( $details as $detail){
                        $detail -> delete();
                    }
                    $user_food -> delete();
                }

                DB::commit();
                return "success";
            }catch (\Exception $e){

                DB::rollback();
                return "fail";
            }
        }

        public function follow_up($patientid = null)
        {
            $result = DB::table(DB::raw('contact_info AS ci'))
                ->select('bc.personid', 'bc.cardid', 'users.name', 'bs.calendar_date', 'ci.start_date', 'ci.med_date', DB::raw("(CASE ci.trace_time WHEN '0000-00-00' THEN ci.start_date ELSE ci.trace_time END) AS trace_time, (CASE ci.trace_method WHEN 1 THEN '电话' WHEN 2 THEN '传真' WHEN 3 THEN 'e-mail' WHEN 4 THEN '回诊讨论' WHEN 5 THEN '网路平台' WHEN 6 THEN '传输线' WHEN 7 THEN '其他' ELSE '' END) AS trace_method,
(CASE ci.contact_time WHEN 1 THEN '早上' WHEN 2 THEN '下午' WHEN 3 THEN '晚上' WHEN 4 THEN '全天' WHEN 5 THEN '其他' ELSE '' END) AS contact_time, ci.hospital_no_uuid"))
                ->join(DB::raw('(SELECT hospital_no_uuid, MAX(calendar_date) AS calendar_date FROM blood_sugar GROUP BY hospital_no_uuid) AS bs'), function($join){ $join->on('ci.hospital_no_uuid', '=', 'bs.hospital_no_uuid'); })
                ->join(DB::raw('(SELECT personid, cardid, hospital_no_uuid, MAX(build_at) FROM buildcases GROUP BY hospital_no_uuid) AS bc'), function($join){ $join->on('ci.hospital_no_uuid', '=', DB::raw('CONVERT(bc.hospital_no_uuid USING utf8) COLLATE utf8_unicode_ci')); })
                ->join('users', 'users.account', '=', 'bc.personid')
                ->orderBy('bc.personid', 'asc')
                ->orderBy('bs.calendar_date', 'desc');

            $count = 0;
            $err_msg = null;
            if($result) {
                $count = $result->count();
                $lists = $result->paginate(10);
            }

            return view('bdata.followup', compact('err_msg', 'count', 'lists'));
        }
    }
