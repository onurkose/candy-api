<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class MapCustomerGroups extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'use_defaults' => 'boolean',
            'groups' => 'array',
            'groups.*.id' => 'hashid_is_valid:'.CustomerGroup::class,
        ];
    }

    public function afterValidator($validator)
    {
        $this->set('use_defaults', $this->use_defaults === null ?: $this->use_defaults);

        if (!$this->use_defaults && !$this->groups) {
            $validator->errors()->add('groups', 'You must specify groups when not using defaults.');
        }
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        $groupData = $this->mapDefaultData();

        if (!$this->use_defaults) {
            $groupData = $this->mapCustomData();
        }

        return $this->model->customerGroups()->sync($groupData);
    }

    protected function mapDefaultData()
    {
        return FetchCustomerGroups::run([
            'paginate' => false
        ])->mapWithKeys(function ($group) {
            return [$group->id => [
                'visible' => false,
                'purchasable' => false,
            ]];
        })->toArray();
    }

    /**
     * Maps customer group data for a model.
     *
     * @param  array  $groups
     * @return array
     */
    protected function mapCustomData()
    {

        $groupData = [];
        foreach ($this->groups as $group) {
            $groupData[(new CustomerGroup)->decodeId($group['id'])] = [
                'visible' => $group['visible'] ?? false,
                'purchasable' => $group['purchasable'] ?? false,
            ];
        }
        return $groupData;
    }
}
