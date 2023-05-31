<?php

declare(strict_types=1);

namespace App\Services;
use Carbon\Carbon;
use App\Organisation;

/**
 * Class OrganisationService
 * @package App\Services
 */
class OrganisationService
{
    /**
     * @param array $attributes
     *
     * @return Organisation
     */
    public function createOrganisation(array $attributes): Organisation
    {
        $organisation = new Organisation();
        $organisation->name = $attributes['name'];
        $organisation->subscribed = $attributes['subscribed'];
        $organisation->trial_end = Carbon::createFromTimestamp($attributes['trial_end']);
   
        $organisation->owner_user_id = $attributes['owner_user_id'];
        // Set any other attributes as needed
    
        // Save the organisation to the database
        $organisation->save();
    
        return $organisation;
    }

   
    public function getOrganisations(string $filter = 'all'): array
    {
        // Logic to retrieve organisations based on the filter
        return [];
    }

}
