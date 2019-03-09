<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Datatables;
use DB;
use App\Models\StudentClass;
use Validator;

class SchoolClassController extends Controller {

    public function addSchoolClass() {

        $all_sections = StudentClass::where(["status" => 1])->get();

        return view("admin.views.add_class", ["sections" => $all_sections]);
    }

    public function listSchoolClasses() {

        return view("admin.views.list_classes");
    }

    public function listAllClasses() {

        $classes_query = DB::table("tbl_classes as class")
                ->select("class.*", "section.section")
                ->leftJoin("tbl_class_sections as section", "class.class_section_id", "=", "section.id")
                ->where(["class.status" => 1])
                ->get();

        return Datatables::of($classes_query)
                        ->editColumn("action_btns", function($classes_query) {

                            return '<a href="#" class="btn btn-info class-section-edit" data-id="' . $classes_query->id . '">Edit</a><a href="#" class="btn btn-danger class-section-delete" data-id="' . $classes_query->id . '">Delete</a>';
                        })
                        ->rawColumns(["action_btns"])
                        ->make(true);
    }

    public function saveClassData(Request $request) {

        $validator = Validator::make(array(
                    "class_name" => $request->class_name,
                    "dd_section" => $request->dd_section,
                    "seats_available" => $request->seats_available
                        ), array(
                    "class_name" => "required",
                    "dd_section" => "required|not_in:-1",
                    "seats_available" => "required"
        ));
        
        if($validator->fails()){
            
            return redirect("add-class")->withErrors($validator)->withInput();
        }else{
            
            $class = new SchoolClass;
            $class->name = $request->class_name;
            $class->class_section_id = $request->dd_section;
            $class->seats_available = $request->seats_available;
            $class->status = $request->dd_status;
            
            $class->save();
            
            $request->session()->flash("message","Class has been created successfully");
            
            return redirect("add-class");
        }
    }

}
