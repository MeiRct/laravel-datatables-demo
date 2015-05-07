<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Carbon\Carbon;
use Datatables;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CollectionController extends Controller
{

    function __construct()
    {
        view()->share('controller', 'CollectionController.php');
    }

    public function getBasic()
    {
        return view('datatables.collection.basic');
    }

    public function getBasicData()
    {
        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)->make();
    }

    public function getBasicObject()
    {
        return view('datatables.collection.basic-object');
    }

    public function getBasicObjectData()
    {
        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)->make(true);
    }

    public function getMultiFilterSelect()
    {
        return view('datatables.collection.multi-filter-select');
    }

    public function getMultiFilterSelectData()
    {
        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)->make(true);
    }

    public function getArray()
    {
        return view('datatables.collection.array');
    }

    public function getArrayData()
    {
        $faker = Faker::create();
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'id'         => $i + 1,
                'name'       => $faker->name,
                'email'      => $faker->email,
                'created_at' => Carbon::now()->format('m-d-Y'),
                'updated_at' => Carbon::now()->format('m-d-Y'),
            ];
        }
        $users = new Collection($data);

        return Datatables::of($users)->make(true);
    }

    public function getAddEditRemoveColumn()
    {
        return view('datatables.collection.add-edit-remove-column');
    }

    public function getAddEditRemoveColumnData()
    {
        $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)
            ->addColumn('action', function ($user) {
                return '<a href="#" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->removeColumn('password')
            ->make(true);
    }

    public function getDtRow()
    {
        return view('datatables.collection.dt-row');
    }

    public function getDtRowData()
    {
        $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)
            ->addColumn('action', function ($user) {
                return '<a href="#" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            })
            ->editColumn('id', '{{$id}}')
            ->removeColumn('password')
            ->setRowId('id')
            ->setRowClass(function ($user) {
                return $user->id % 2 == 0 ? 'alert-success' : 'alert-warning';
            })
            ->setRowData([
                'id' => 'test',
            ])
            ->setRowAttr([
                'color' => 'red',
            ])
            ->make(true);
    }

    public function getCustomFilter()
    {
        return view('datatables.collection.custom-filter');
    }

    public function getCustomFilterData(Request $request)
    {
        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)
            ->filter(function ($instance) use ($request) {
                if ($request->has('name')) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains($row['name'], $request->get('name')) ? true : false;
                    });
                }

                if ($request->has('email')) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains($row['email'], $request->get('email')) ? true : false;
                    });
                }
            })
            ->make(true);
    }

    public function getCarbon()
    {
        return view('datatables.collection.carbon');
    }

    public function getCarbonData()
    {
        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])->get();

        return Datatables::of($users)
            ->editColumn('created_at', '{!! $created_at->diffForHumans() !!}')
            ->editColumn('updated_at', function ($user) {
                return $user->updated_at->format('Y/m/d');
            })
            ->make(true);
    }

    public function getGithub()
    {
        return view('datatables.collection.github');
    }

    public function getGithubData(Request $request)
    {
        $search = $request->get('search');
        $keyword = $search['value']?: 'laravel';
        $repositories = \Cache::get($keyword, function() use($keyword) {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.github.com/search/repositories', [
                    'query' => ['q' => $keyword]
                ]);
            $repositories = $response->json();
            \Cache::put($keyword, $repositories, 1);

            return $repositories;
        });

        $data = new Collection($repositories['items']);

        return Datatables::of($data)
            ->editColumn('full_name', function($row) {
                return \HTML::link($row['url'], $row['full_name']);
            })
            ->editColumn('private', function($row) {
                return $row['private'] ? 'Y' : 'N';
            })
            ->filter(function(){}) // disable built-in search function
            ->make(true);
    }

}
