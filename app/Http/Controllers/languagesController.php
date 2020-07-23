<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class languagesController extends Controller
{
    

    public function getLanguages(Request $request) {
        $client = new Client();
        $res = $client->request('GET', 'https://api.github.com/search/repositories?q=created:2020-07-22&sort=stars&order=desc&per_page=100&page=1');
        $data = $res->getBody()->getContents();
        $json = json_decode($data);
        $combined= [];

        foreach ($json->items as $row) {
            if(empty($combined) || !collect($combined)->contains('language',$row->language)) {
                $combined = $this->insertForFirstTime($combined, $row);
            }
            else {
                $this->addToExsitingList($combined, $row);
            }
        }
        dd(collect($combined));
    }

    ////Function to insert for the first time to our list
    public function insertForFirstTime($combined, $row) {
        $collection = collect(['language','nbr Of Repos','repos']);
        $repos = [];
        array_push($repos,$row);
        array_push($combined, $collection->combine([$row->language,1,$repos]));
        return $combined;
    }

    //// Function to add to existing list
    public function addToExsitingList($combined, $row) {
        collect($combined)->each(function ($item, $key) use($row) {
            if($item['language'] == $row->language) {
                $item['nbr Of Repos'] = $item['nbr Of Repos'] + 1;
                $repos = collect($item['repos'])->toArray();
                array_push($repos,$row);
                $item['repos'] = $repos;
            }
        });
    }
}