<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    // Define the constant for the controller name
    const CONTROLLER_NAME = "App\Http\Controllers\CountryController";

    // Country Methods
    public function countryList(Request $request)
    {
        try {
            $params = ['search' => $request->search ?? null, 'row' => $request->perPage];
            $list = $this->_countryList(...$params);
            return $this->actionSuccess('Countries List get successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function getStateCityId(Request $request)
    {
        try {
            $country_id = Country::where('name', "India")->first()->id;
            $state_id = State::where('name', $request->state)->first()->id;
            $city_id = City::where('name', $request->city)->first()->id;
            
            $dt = [
                "country_id" => $country_id,
                "state_id" => $state_id,
                "city_id" => $city_id,
            ];
             return $this->actionSuccess('Countries List get successfully.', $dt);
        } catch (\Exception $e) {
            //Exception
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    


    public function _countryList(string $search = null, int $row = 25)
    {
        $query = Country::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        return $query->latest()->paginate($row);
    }

    public function countryCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'iso3' => 'required',
            'iso2' => 'required',
            'phone_code' => 'required',
            'currency_name' => 'required',
            'currency_symbol' => 'required',
            'country_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Country::where('name', $request->name)->exists()) {
            return $this->actionFailure("Country Already available!");
        }

        $country_id = Country::Latest()->pluck('country_id')->first() ?? 300;

        try {
            DB::beginTransaction();
            $country = [];
            $create = [
                'country_id' => $country_id + 1,
                'name' => $request->name,
                'iso3' => $request->iso3,
                'iso2' => $request->iso2,
                'numeric_code' => $request->numeric_code,
                'phone_code' => $request->phone_code,
                'capital' => $request->capital,
                'currency' => $request->currency,
                'currency_name' => $request->currency_name,
                'currency_symbol' => $request->currency_symbol,
                'region' => $request->region,
                'nationality' => $request->nationality,
                'timezones' => $request->timezones,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'emoji' => $request->emoji,
                'emojiU' => $request->emojiU,
                'country_type' => $request->country_type
            ];

            $country = Country::create($create);
            DB::commit();
            return $this->actionSuccess('Country created successfully.', $country);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to create country: ' . $e->getMessage());
        }
    }

    public function dropdownCountryList(Request $request)
    {
        try {
            $countries = Country::select('id', 'name')->get();
            return $this->actionSuccess('Dropdown countries retrieved successfully.', $countries);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function countryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'iso3' => 'required',
            'iso2' => 'required',
            'phone_code' => 'required',
            'currency_name' => 'required',
            'currency_symbol' => 'required',
            'country_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = Country::where('id', '!=', $request->id)->where('name', $request->name)->exists();
        if ($exists) {
            return $this->actionFailure("Country Already available!");
        }

        try {
            DB::beginTransaction();
            $update = [
                'name' => $request->name,
                'iso3' => $request->iso3,
                'iso2' => $request->iso2,
                'numeric_code' => $request->numeric_code,
                'phone_code' => $request->phone_code,
                'capital' => $request->capital,
                'currency' => $request->currency,
                'currency_name' => $request->currency_name,
                'currency_symbol' => $request->currency_symbol,
                'region' => $request->region,
                'nationality' => $request->nationality,
                'timezones' => $request->timezones,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'emoji' => $request->emoji,
                'emojiU' => $request->emojiU,
                'country_type' => $request->country_type
            ];

            $country = Country::where('id', $request->id)->first();
            if ($country) {
                $country->update($update);
                State::where('country_id', $request->id)->update(['country_name' => $country->name]);
                City::where('country_id', $request->id)->update(['country_name' => $country->name]);
                DB::commit();
                return $this->actionSuccess('Country updated successfully.', $country);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Country updated Request Failed');
    }

    public function countryDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        $exists = UserAddress::where('country_id', $request->id)->exists();
        if ($exists) {
            return $this->actionFailure("If the country already uses any user, please do not delete it!");
        }
        try {
            DB::beginTransaction();
            $country = Country::find($request->id);
            if ($country) {
                $country->delete();
                DB::commit();
                return $this->actionSuccess('Country deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Country deleted Request Failed');
    }

    // State Methods
    public function stateList(Request $request)
    {
        try {
            $params = ['country_id' => $request->country_id ?? null, 'search' => $request->search ?? null, 'row' => $request->perPage];
            $list = $this->_stateList(...$params);
            return $this->actionSuccess('State List get successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function _stateList(string $country_id = null, string $search = null, int $row = 25)
    {
        $query = State::query();
        if ($country_id) {
            $query->where('country_id', $country_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        return $query->latest()->paginate($row);
    }

    public function stateCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country_id' => 'required',
            'state_code' => 'required',
            'type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'state_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = State::where('country_id', $request->country_id)->where('name', $request->name)->exists();
        if ($exists) {
            return $this->actionFailure("State Already available!");
        }

        try {
            DB::beginTransaction();
            $country_name = Country::where('id',$request->country_id)->pluck('name')->first();
            $create = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'country_name' => $country_name,
                'state_code' => $request->state_code,
                'type' => $request->type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'state_type' => $request->state_type,
            ];

           $state= State::create($create);
            DB::commit();
            return $this->actionSuccess('State created successfully.', $state);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to create state: ' . $e->getMessage());
        }
    }

    public function dropdownStateList(Request $request)
    {
        try {
            $query = State::query();
            if ($request->country_id) {
                $query->where('country_id', $request->country_id);
            }
            $states =  $query->select('id', 'name')->get();
            return $this->actionSuccess('Dropdown states retrieved successfully.', $states);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve dropdown states: ' . $e->getMessage());
        }
    }

    public function stateUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'country_id' => 'required',
            'state_code' => 'required',
            'type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'state_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = State::where('id', '!=', $request->id)->where('country_id', $request->country_id)->where('name', $request->name)->exists();
        if ($exists) {
            return $this->actionFailure("Country Already available!");
        }

        try {
            DB::beginTransaction();
            $country_name = Country::where('id',$request->country_id)->pluck('name')->first();
            $update = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'country_name' => $country_name,
                'state_code' => $request->state_code,
                'type' => $request->type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'state_type' => $request->state_type,
            ];

            $state = State::where('id', $request->id)->first();
            if ($state) {
                $state->update($update);
                City::where('state_id', $request->id)->update(['state_name' => $state->name]);
                DB::commit();
                return $this->actionSuccess('State updated successfully.', $state);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to update state: ' . $e->getMessage());
        }
        return $this->actionFailed('State update Request failed!');
    }

    public function stateDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        $exists = UserAddress::where('state_id', $request->id)->exists();
        if ($exists) {
            return $this->actionFailure("If the State already uses any user, please do not delete it!");
        }
        try {
            DB::beginTransaction();
            $state = State::where('id', $request->id)->first();
            if ($state) {
                $state->delete();
                DB::commit();
                return $this->actionSuccess('State deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('State deleted Request Failed');
    }

    // City Methods
    public function cityList(Request $request)
    {
        try {
            $params = ['country_id' => $request->country_id ?? null, 'state_id' => $request->state_id ?? null, 'search' => $request->search ?? null, 'row' => $request->perPage];
            $list = $this->_cityList(...$params);
            return $this->actionSuccess('City List get successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve cities: ' . $e->getMessage());
        }
    }

    private function _cityList(string $country_id = null, string $state_id = null, string $search = null, int $row = 25)
    {
        $query = City::query();
        if ($country_id) {
            $query->where('country_id', $country_id);
        }

        if ($state_id) {
            $query->where('state_id', $state_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        return $query->latest()->paginate($row);
    }

    public function cityCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'city_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = City::where('country_id', $request->country_id)->where('state_id', $request->state_id)->where('name', $request->name)->exists();
        if ($exists) {
            return $this->actionFailure("City Already available!");
        }

        try {
            DB::beginTransaction();
            $country_name = Country::where('id',$request->country_id)->pluck('name')->first();
            $state_name = State::where('id',$request->state_id)->pluck('name')->first();
            $create = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'country_name' => $country_name,
                'state_id' => $request->state_id,
                'state_name' => $state_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'city_type' => $request->city_type,
            ];

            $city = City::create($create);
            DB::commit();
            return $this->actionSuccess('City created successfully.', $city);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to create city: ' . $e->getMessage());
        }
    }

    public function dropdownCityList(Request $request)
    {
        try {
            $query = City::query();
            if ($request->country_id) {
                $query->where('country_id', $request->country_id);
            }
            if ($request->state_id) {
                $query->where('state_id', $request->state_id);
            }
            $city =  $query->select('id', 'name')->get();
            return $this->actionSuccess('Dropdown cities retrieved successfully.', $city);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve dropdown cities: ' . $e->getMessage());
        }
    }

    public function cityUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'city_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = City::where('id', '!=', $request->id)->where('country_id', $request->country_id)->where('state_id', $request->state_id)->where('name', $request->name)->exists();
        if ($exists) {
            return $this->actionFailure("Country Already available!");
        }
        try {
            DB::beginTransaction();
           $country_name = Country::where('id',$request->country_id)->pluck('name')->first();
           $state_name = State::where('id',$request->state_id)->pluck('name')->first();
            $update = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'country_name' => $country_name,
                'state_id' => $request->state_id,
                'state_name' => $state_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'city_type' => $request->city_type,
            ];

            $city = City::where('id', $request->id)->first();
            if ($city) {
                $city->update($update);
                DB::commit();
                return $this->actionSuccess('City updated successfully.', $city);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to update city: ' . $e->getMessage());
        }
        return $this->actionFailed('City update Request failed!');
    }

    public function cityDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        $exists = UserAddress::where('city_id', $request->id)->exists();
        if ($exists) {
            return $this->actionFailure("If the City already uses any user, please do not delete it!");
        }

        try {
            DB::beginTransaction();
            $city = City::where('id', $request->id)->first();
            if ($city) {
                $city->delete();
                DB::commit();
                return $this->actionSuccess('City deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('City deleted Request Failed');
    }
}
