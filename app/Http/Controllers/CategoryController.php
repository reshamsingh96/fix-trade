<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\CategoryController";

    // Method to list product categories
    public function categoryList(Request $request)
    {
        try {
            $params = ['search' => $request->search ?? null, 'row' => $request->perPage];
            $list = $this->_categoryList(...$params);
            return $this->actionSuccess('categories retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function _categoryList(string $search = null, int $row = 25)
    {
        $query = Category::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        return $query->latest()->paginate($row);
    }

    public function dropdownCategoryList(Request $request)
    {
        try {
            $query = Category::query();
            $states =  $query->select('id', 'name','category_url')->get();
            return $this->actionSuccess('Dropdown Category retrieved successfully.', $states);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // Method to create a new category
    public function categoryCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_type' => 'required', // add (yes ,no)
            'description' => 'required',
            // 'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Category::where('name', $request->name)->exists()) {
            return $this->actionFailure("Category Already Exists!");
        }

        try {
            DB::beginTransaction();
            $category_url = null;
            $image_public_id = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'category',
                    // 'transformation' => [
                    //     'width' => 300,
                    //     'height' => 300,
                    //     'crop' => 'fit'
                    // ]
                ]);
                $category_url = $uploadResult->getSecurePath();
                $image_public_id = $uploadResult->getPublicId();
            }

            $create = [
                'name' => $request->name,
                'category_type' => $request->category_type,
                'category_url' => $category_url,
                'image_public_id' => $image_public_id,
                'description' => $request->description,
            ];
            $category = Category::create($create);
            DB::commit();
            return $this->actionSuccess('category created successfully.', $category);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // Method to update a category
    public function categoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            // 'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'category_type' => 'required', // add (yes ,no)
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Category::where('id', '!=', $request->id)->where('name', $request->name)->exists()) {
            return $this->actionFailure("Category Already Exists!");
        }

        try {
            DB::beginTransaction();
            $category_url = null;
            $update = [
                'name' => $request->name,
                'category_type' => $request->category_type,
                'category_url' => $category_url,
                'description' => $request->description,
            ];
            $category = Category::where('id', $request->id)->first();
            if ($category) {
                $category->update($update);

                if ($request->hasFile('image')) {
                    if ($category->image_public_id) {
                        Cloudinary::destroy($category->image_public_id);
                    }

                    // Upload the new image
                    $uploadedFile = $request->file('image');
                    $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                        'folder' => 'category',
                    ]);
                    $category->category_url = $uploadResult->getSecurePath();
                    $category->image_public_id = $uploadResult->getPublicId();
                    $category->save();
                }

                DB::commit();
                return $this->actionSuccess('Category updated successfully.', $category);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Category update request failed');
    }

    // Method to delete a product category
    public function categoryDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Product::where('category_id', $request->id)->exists()) {
            return $this->actionFailure("Category Already Use to Product");
        }
        try {
            DB::beginTransaction();
            $category = Category::where('id', $request->id)->first();
            if ($category) {
                if ($category->image_public_id) {
                    Cloudinary::destroy($category->image_public_id);
                }

                $category->delete();
                DB::commit();
                return $this->actionSuccess('category deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Category Delete request failed');
    }

    // Method to list product subcategories
    public function subCategoryList(Request $request)
    {
        try {
            $params = ['search' => $request->search ?? null, 'category_id' => $request->category_id ?? null, 'row' => $request->perPage];
            $list = $this->_subCategoryList(...$params);
            return $this->actionSuccess('Sub Category retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    public function _subCategoryList(string $search = null, string $category_id = null, int $row = 25)
    {
        $query = SubCategory::query();

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        return $query->latest()->paginate($row);
    }

    public function dropdownSubCategoryList(Request $request)
    {
        try {
            $query = SubCategory::query();
            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }
            $states =  $query->select('id', 'name')->get();
            return $this->actionSuccess('Dropdown Sub Category retrieved successfully.', $states);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // Method to create a new subcategory
    public function subCategoryCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
            // 'image' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'sub_category_type' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (SubCategory::where('name', $request->name)->exists()) {
            return $this->actionFailure("Sub Category Already Exists!");
        }
        try {
            DB::beginTransaction();
            $sub_category_url = null;
            $image_public_id = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'category',
                ]);
                $sub_category_url = $uploadResult->getSecurePath();
                $image_public_id = $uploadResult->getPublicId();
            }

            $create = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                'sub_category_type' => $request->sub_category_type,
                'sub_category_url' => $sub_category_url,
                'image_public_id' => $image_public_id,
                'description' => $request->description,
            ];
            SubCategory::create($create);
            DB::commit();
            $params = ['search' => $request->search ?? null, 'category_id' => $request->category_id ?? null, 'row' => $request->perPage];
            $list = $this->_subCategoryList(...$params);
            return $this->actionSuccess('Sub Category created successfully.', $list);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }

    // Method to update a subcategory
    public function subCategoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'name' => 'required',
            // 'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'sub_category_type' => 'required', // add (yes ,no)
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (SubCategory::where('id', '!=', $request->id)->where('category_id', $request->category_id)->where('name', $request->name)->exists()) {
            return $this->actionFailure("Sub Category Already Exists!");
        }

        try {
            DB::beginTransaction();
            $sub_category_url = null;
            $update = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                'sub_category_type' => $request->sub_category_type,
                'sub_category_url' => $sub_category_url,
                'description' => $request->description,
            ];
            $subcategory = SubCategory::where('id', $request->id)->first();
            if ($subcategory) {
                $subcategory->update($update);
                if ($request->hasFile('image')) {
                    if ($subcategory->image_public_id) {
                        Cloudinary::destroy($subcategory->image_public_id);
                    }

                    // Upload the new image
                    $uploadedFile = $request->file('image');
                    $uploadResult = Cloudinary::upload($uploadedFile->getRealPath(), [
                        'folder' => 'category',
                    ]);
                    $subcategory->sub_category_url = $uploadResult->getSecurePath();
                    $subcategory->image_public_id = $uploadResult->getPublicId();
                    $subcategory->save();
                }

                DB::commit();
                return $this->actionSuccess('Sub Category updated successfully.', $subcategory);
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Sub Category updated Request Failed');
    }

    // Method to delete a subcategory
    public function subCategoryDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }

        if (Product::where('sub_category_id', $request->id)->exists()) {
            return $this->actionFailure("Sub Category Already Use to Product");
        }

        try {
            DB::beginTransaction();
            $sub_category = SubCategory::where('id', $request->id)->first();
            if ($sub_category) {
                if ($sub_category->image_public_id) {
                    Cloudinary::destroy($sub_category->image_public_id);
                }
                $sub_category->delete();
                DB::commit();
                return $this->actionSuccess('Sub Category deleted successfully.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Sub Category Delete request failed');
    }
}
