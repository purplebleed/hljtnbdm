<?php namespace App\Http\Controllers\Patient;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Patientprofile;
use Illuminate\Http\Request;

use App\Model\Pdata\HospitalNo;
use Auth;

class PatientprofileController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$patientprofiles = Patientprofile::all();

		return view('patient.index', compact('patientprofiles'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('patient.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$patientprofile = new Patientprofile();

                $patientprofile->pp_patientid = $request->input("pp_patientid");
                $patientprofile->pp_personid = $request->input("pp_personid");
//                $patientprofile->account = $request->input("account");
                $patientprofile->pp_name = $request->input("pp_name");
                $patientprofile->pp_birthday = $request->input("pp_birthday");
                $patientprofile->pp_sex = $request->input("pp_sex");
                $patientprofile->pp_height = $request->input("pp_height");
                $patientprofile->pp_weight = $request->input("pp_weight");
                $patientprofile->pp_tel1 = $request->input("pp_tel1");
                $patientprofile->pp_tel2 = $request->input("pp_tel2");
                $patientprofile->pp_mobile1 = $request->input("pp_mobile1");
                $patientprofile->pp_mobile2 = $request->input("pp_mobile2");
                $patientprofile->pp_address = $request->input("pp_address");
                $patientprofile->pp_email = $request->input("pp_email");

		$patientprofile->save();

        //create the hospital_no
        while(1){
            $uuid = uniqid('cn_');
            if(HospitalNo::find($uuid) == null){
                break;
            }
        }
        $hospital_no = new HospitalNo();
        $hospital_no -> hospital_no_uuid = $uuid;
        $hospital_no -> patient_user_id = $request->input("pp_personid");
        $hospital_no -> nurse_user_id = Auth::user() -> id;
        $hospital_no -> hospital_no_displayname = substr($request->input("pp_patientid"),0,-6).'xxxxxx';
        $hospital_no -> save();

		return redirect()->route('patient.index')->with('message', '项目成功创建。');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$patientprofile = Patientprofile::findOrFail($id);

		return view('patient.show', compact('patientprofile'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$patientprofile = Patientprofile::findOrFail($id);

		return view('patient.edit', compact('patientprofile'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @param Request $request
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'pp_name' => 'required',
			'pp_email' => 'required|email'
		]);

		$patientprofile = Patientprofile::findOrFail($id);

                $patientprofile->pp_patientid = $request->input("pp_patientid");
                $patientprofile->pp_personid = $request->input("pp_personid");
//                $patientprofile->account = $request->input("account");
                $patientprofile->pp_name = $request->input("pp_name");
                $patientprofile->pp_birthday = $request->input("pp_birthday");
                $patientprofile->pp_sex = $request->input("pp_sex");
                $patientprofile->pp_height = $request->input("pp_height");
                $patientprofile->pp_weight = $request->input("pp_weight");
                $patientprofile->pp_tel1 = $request->input("pp_tel1");
                $patientprofile->pp_tel2 = $request->input("pp_tel2");
                $patientprofile->pp_mobile1 = $request->input("pp_mobile1");
                $patientprofile->pp_mobile2 = $request->input("pp_mobile2");
                $patientprofile->pp_address = $request->input("pp_address");
                $patientprofile->pp_email = $request->input("pp_email");

		$patientprofile->save();

		return redirect()->route('patient.index')->with('message', '项目成功更新。');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$patientprofile = Patientprofile::findOrFail($id);
		$patientprofile->delete();

		return redirect()->route('patient.index')->with('message', '项目成功删除。');
	}

}
