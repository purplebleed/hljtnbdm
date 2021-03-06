<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Hasfeature extends Model
{

    protected $table = 'hasfeatures';

    protected $fillable = ['user_id', 'feature_id'];

    protected static function rules()
    {
        return [
            'user_id' => 'required',
            'feature_id' => "required|unique:hasfeatures,feature_id,0,id,user_id,user_id",
        ];
    }

    /**
     *
     * 一对多关联
     */
    public function feature()
    {
        return $this->belongsTo('App\Feature');
    }

}
