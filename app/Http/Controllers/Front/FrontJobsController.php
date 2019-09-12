<?php

namespace App\Http\Controllers\Front;

use App\Helper\Reply;
use App\Http\Requests\FrontJobApplication;
use App\Job;
use App\JobApplication;
use App\JobApplicationAnswer;
use App\JobCategory;
use App\JobLocation;
use App\JobQuestion;
use App\Notifications\NewJobApplication;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;

class FrontJobsController extends FrontBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.front.jobOpenings');
    }

    public function jobOpenings()
    {
        $this->jobs = Job::activeJobs();
        $this->locations = JobLocation::all();
        $this->categories = JobCategory::all();
        return view('front.job-openings', $this->data);
    }

    public function jobDetail($slug)
    {
        $this->job = Job::where('slug', $slug)->where('status', 'active')->firstOrFail();
        return view('front.job-detail', $this->data);
    }

    public function jobApply($slug)
    {
        $this->job = Job::where('slug', $slug)->first();
        $this->jobQuestion = JobQuestion::with(['question'])->where('job_id', $this->job->id)->get();

        return view('front.job-apply', $this->data);
    }

    public function saveApplication(FrontJobApplication $request)
    {
        $jobApplication = new JobApplication();
        $jobApplication->full_name = $request->full_name;
        $jobApplication->job_id = $request->job_id;
        $jobApplication->status_id = 1; //applied status id
        $jobApplication->email = $request->email;
        $jobApplication->phone = $request->phone;
        $jobApplication->cover_letter = $request->cover_letter;
        $jobApplication->column_priority = 0;

        if ($request->hasFile('resume')) {
            $jobApplication->resume = $request->resume->hashName();
            $request->resume->store('user-uploads/resumes');
        }

        if ($request->hasFile('photo')) {
            $jobApplication->photo = $request->photo->hashName();
            $request->photo->store('user-uploads/candidate-photos');
        }
        $jobApplication->save();


        $users = User::allAdmins();
        if (!empty($request->answer)) {
                foreach ($request->answer as $key => $value) {
                    $answer = new JobApplicationAnswer();
                    $answer->job_application_id = $jobApplication->id;
                    $answer->job_id = $request->job_id;
                    $answer->question_id = $key;
                    $answer->answer = $value;
                    $answer->save();
                }
            }

        Notification::send($users, new NewJobApplication($jobApplication));

        return Reply::dataOnly(['status' => 'success', 'msg' => __('modules.front.applySuccessMsg')]);
    }
}
