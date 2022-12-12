<?php

namespace DTApi\Http\Controllers;

use Exception;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * 
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * BookingController constructor.
     */
    public function __construct(protected BookingRepository $bookingRepository)
    {
    }

    public function index(Request $request): Response
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif (
            in_array($request->user()->user_type, [User::ADMIN_ROLE_ID, User::SUPERADMIN_ROLE_ID])
        ) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }
    
    public function show(int $id): Response
    {
        return response(
            $this->repository
                ->with('translatorJobRel.user')
                ->find($id)
        );
    }

    public function store(Request $request): Response
    {
        $response = $this
            ->repository
            ->store(
                $request->user(),
                $request->all()
            );

        return response($response);
    }

    public function update(int $id, Request $request): Response
    {
        return response(
            $this
                ->repository
                ->updateJob(
                    $id,
                    $request->except('_token', 'submit'),
                    $request->user()
                )
        );
    }

    public function immediateJobEmail(Request $request): Response
    {
        return response(
            $this
                ->repository
                ->storeJobEmail($request->all())
        );
    }

    public function getHistory(Request $request): ?Response
    {
        if ($user_id != $request->get('user_id')) {
            return null;
        }

        return response(
            $this
                ->repository
                ->getUsersJobsHistory($user_id, $request)
        );
    }

    public function acceptJob(Request $request): Response
    {
        return response(
            $this->repository->acceptJob(
                $request->all(),
                $request->user()
            )
        );
    }

    public function acceptJobWithId(Request $request): Response
    {
        return response(
            $this->repository->acceptJobWithId(
                $request->get('job_id'),
                $request->user()
            )
        );
    }

    public function cancelJob(Request $request): Response
    {
        return response(
            $this->repository->cancelJobAjax(
                $request->all(),
                $request->user()
            )
        );
    }

    public function endJob(Request $request): Response
    {
        return response(
            $this->repository->endJob($request->all())
        );
    }

    public function customerNotCall(Request $request): Response
    {
        return response(
            $this->repository->customerNotCall(
                $request->all()
            )
        );
    }

    public function getPotentialJobs(Request $request): Response
    {
        return response(
            $this->repository->getPotentialJobs($request->user())
        );
    }

    public function distanceFeed(Request $request): string|Response
    {
        $admincomment = $request->get('admincomment');
        $flagged = $request->get('flagged') == 'true' ? 'yes' : 'no';

        if ($flagged == 'yes' && ! $admincomment) {
            return "Please, add comment";
        }

        $distance = $request->get('distance');
        $time = $request->get('time');
        $jobid = $request->get('jobid');
        $session_time = $request->get('session_time');
        $manually_handled = $request->get('manually_handled') == 'true' ? 'yes' : 'no';
        $by_admin = $request->get('by_admin') == 'true' ? 'yes' : 'no';

        if ($time || $distance) {
            Distance::query()
                ->whereJobId($jobid)
                ->update(compact('distance', 'time'));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::query()
                ->whereId($jobid)
                ->update(compact('admin_comments', 'flagged', 'session_time', 'manually_handled', 'by_admin'));
        }

        return response('Record updated!');
    }

    public function reopen(Request $request): Response
    {
        return response(
            $this->repository->reopen(
                $request->all()
            )
        );
    }

    public function resendNotifications(Request $request): Response
    {
        try {
            $job = $this->repository->find(
                $request->jobid
            );
            
            $this
                ->repository
                ->sendNotificationTranslator(
                    $job,
                    $this->repository->jobToData($job),
                    '*'
                );
    
            return response(['success' => 'Push sent']);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()]);
        }
    }

    /**
     * Sends SMS to Translator
     */
    public function resendSMSNotifications(Request $request): Response
    {
        try {
            $this
                ->repository
                ->sendSMSNotificationToTranslator(
                    $this->repository->find(
                        $request->jobid
                    )
                );

            return response(['success' => 'SMS sent']);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()]);
        }
    }
}
