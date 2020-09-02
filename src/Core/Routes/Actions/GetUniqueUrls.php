<?php

namespace GetCandy\Api\Core\Routes\Actions;


use GetCandy\Api\Core\Scaffold\AbstractAction;

class GetUniqueUrls extends AbstractAction
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
            'urls' => 'array'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        $unique = [];

        if (is_array($urls)) {
            $previousUrl = null;
            foreach ($urls as $locale => $url) {
                $i = 1;
                while (GetCandy::routes()->slugExists($url, $path) || $previousUrl == $url) {
                    $url = $url.'-'.$i;
                    $i++;
                }
                $unique[] = [
                    'locale' => $locale,
                    'path' => $path,
                    'slug' => $url,
                    'default' => $locale == app()->getLocale() ? true : false,
                ];
                $previousUrl = $url;
            }
        } else {
            $i = 1;
            $url = $urls;
            while (GetCandy::routes()->slugExists($url)) {
                $url = $url.'-'.$i;
                $i++;
            }
            $unique[] = [
                'locale' => app()->getLocale(),
                'slug' => $url,
                'default' => true,
            ];
        }

        return $unique;
    }
}
