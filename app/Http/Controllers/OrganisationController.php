<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Organisation;
use App\Transformers\UserTransformer;
use App\Transformers\OrganisationTransformer;
use App\Services\OrganisationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helper\JsonApiResponse;
/**
 * Class OrganisationController
 * @package App\Http\Controllers
 */
class OrganisationController extends ApiController
{
    /**
     * Store a new organisation.
     *
     * @param Request $request
     * @param OrganisationService $service
     *
     * @return JsonResponse
     */
    public function store(Request $request, OrganisationService $service): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $organisationData = [
            'name' => $request->input('name'),
            'subscribed' => false,
            'trial_end' => Carbon::now()->addDays(30)->timestamp,
        ];

        /** @var Organisation $organisation */
        $user = auth()->user();
        $organisationData['owner_user_id'] = $user->id;
        $organisation = $service->createOrganisation($organisationData);

        // Send confirmation email
        
        $this->sendConfirmationEmail($user, $organisation);

        // Transform the response data
    
        $transformer = new UserTransformer();
        $transformedUser = $transformer->transform($user);

        $response = [
            'organisation' => $organisation,
            'user' => $transformedUser,
        ];
        return JsonApiResponse::success('Successfully Created Organisation.', $response);
        
    }

    /**
     * Send a confirmation email to the user.
     *
     * @param User $user
     * @param Organisation $organisation
     *
     * @return void
     */
    private function sendConfirmationEmail(\App\User $user, Organisation $organisation)
    {
        $emailContent = "Dear " . $user->name . ",\n\n";
        $emailContent .= "Congratulations! Your organization, " . $organisation->name . ", has been created.\n";
        $emailContent .= "Trial period: 30 days.\n\n";
        $emailContent .= "Thank you,\nYour Company";

        // Code to send email
        // Replace the placeholders below with your email sending logic
        $to = $user->email;
        $subject = "Organization Creation Confirmation";
        $headers = "From: yourcompany@example.com";

        mail($to, $subject, $emailContent, $headers);
    }

    /**
     * List all organisations.
     *
     * @param OrganisationService $service
     *
     * @return JsonResponse
     */
    public function listAll(OrganisationService $service)
    {
        $filter = $_GET['filter'] ?? false;
        $organisations = Organisation::all();

        $organisationArray = [];

        foreach ($organisations as $organisation) {
            if ($filter === 'subbed' && $organisation->subscribed == 1) {
                $organisationArray[] = $organisation;
            } elseif ($filter === 'trail' && $organisation->subscribed == 0) {
                $organisationArray[] = $organisation;
            } elseif (!$filter) {
                $organisationArray[] = $organisation;
            }
        }

        $transformedOrganisations = fractal($organisationArray, new OrganisationTransformer())->toArray();

        return response()->json($transformedOrganisations);
    }

}
