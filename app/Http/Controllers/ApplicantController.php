<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Applicant;
use App\Mail\JobApplied;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    use AuthorizesRequests;

    public function index(Job $job): View
    {
        $this->authorize('update', $job);
        $applicants = $job->applicants()->with('user')->paginate(10);
        return view('jobs.applicants', compact('job', 'applicants'));
    }

    // save new job application
    // POST /jobs/{job}/apply
    public function store(Request $request, Job $job): RedirectResponse
    {
        if (Applicant::where('job_id', $job->id)->where('user_id', auth()->id())->exists()) {
            return redirect()->back()->with('error', 'You have already applied to this job.');
        }

        $validatedData = $request->validate([
            'full_name' => 'required|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string',
            'message' => 'nullable|string',
            'location' => 'nullable|string',
            'resume_path' => 'required|file|mimes:pdf|max:2048',
        ]);

        if ($request->hasFile('resume_path')) {
            $validatedData['resume_path'] = $request->file('resume_path')->store('resumes', 'public');
        }
        $validatedData['job_id'] = $job->id;
        $validatedData['user_id'] = auth()->user()->id;

        try {
            $applicant = Applicant::create($validatedData);
            try {
                Mail::to($job->user->email)->send(new JobApplied($applicant, $job));
            } catch (Exception $e) {
                Log::error('Failed to send job application email: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', 'Your application has been submitted.');
        } catch (Exception $e) {
            Log::error('Failed to create application: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit application: ' . $e->getMessage());
        }
    }

    public function destroy(Job $job, Applicant $applicant): RedirectResponse
    {
        $this->authorize('update', $job);
        
        if (!empty($applicant->resume_path)) {
            Storage::delete('public/' . $applicant->resume_path);
        }

        $applicant->delete();
        return redirect()->back()->with('success', 'The application has been removed.');
    }
}
