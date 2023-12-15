<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Manual;
use App\Models\ManualCategory;
use App\Models\ManualCategoryLevel1;
use App\Models\ManualCategoryLevel2;
use App\Models\ManualTagMaster;
use App\Models\Shop;
use App\Models\User;
use App\Rules\Import\OrganizationRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ManualCsvImport implements
    ToCollection,
    WithCalculatedFormulas,
    WithStartRow,
    WithValidation
{
    use Importable;

    private $brand = [];
    
    public function __construct()
    {
        $admin = session('admin');
        $this->brand = $this->getBrandNameArray($admin->organization1_id);
        array_push(($this->brand), "全て");
    }

    public function collection(Collection $rows)
    {
        //
        $admin = session("admin");
        $organization1_id = $admin->organization1_id;

        foreach ($rows as $index => [
            $no,
            $new_cateory,
            $category,
            $title,
            $tag1,
            $tag2,
            $tag3,
            $tag4,
            $tag5,
            $start_datetime,
            $end_datetime,
            $status,
            $brand,
            $description
        ]) {
            $manual = Manual::where('number', $no)
                ->where('organization1_id', $organization1_id)
                ->first();
            $row_contents = $rows[$index]->slice(13);
            
            if($manual->content) {
                foreach ($manual->content as $key => $content) {
                    $content_title = $row_contents[($key * 2) + 14] ?? '';
                    $content_description = $row_contents[($key * 2) + 15] ?? '';
                    $content->title = $content_title;
                    $content->description = $content_description;
                    $content->save();
                }
            }
            $brand_param = ($brand == "全て") ? $this->getBrandIdArray($organization1_id) : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
            $new_category_array = $new_cateory ? explode('|', $new_cateory) : null;
            $new_category_level1_name = isset($new_category_array[0]) ? str_replace(' ', '', trim($new_category_array[0], "\"")) : NULL;
            $new_category_level2_name = isset($new_category_array[1]) ? str_replace(' ', '', trim($new_category_array[1], "\"")) : NULL;

            $manual->category_id = $category ? ManualCategory::where('name', $category)->pluck('id')->first() : NULL;
            $manual->category_level1_id = isset($new_category_array[0]) ? ManualCategoryLevel1::where('name', $new_category_level1_name)->pluck('id')->first() : NULL;
            $manual->category_level2_id = isset($new_category_array[1]) ? ManualCategoryLevel2::where('name', $new_category_level2_name)->pluck('id')->first() : NULL;
            $manual->title = $title;
            $manual->start_datetime = $this->parseDateTime($start_datetime);
            $manual->end_datetime = $this->parseDateTime($end_datetime);
            $manual->description = $description;
            $manual->save();
            $manual->tag()->sync($this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]));
            $manual->brand()->sync($brand_param);
            $manual->user()->sync(
                !$manual->save ? $this->targetUserParam($brand_param) : []
            );
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    // public function prepareForValidation($data, $index)
    // {
    //     // $data['13'] = $this->strToArray($data['13']);
    //     // $data['14'] = $this->strToArray($data['14']);
    //     // $data['15'] = $this->strToArray($data['15']);
    //     // return $data;
    // }

    public function rules(): array
    {
        return [
            '0' => ['required'],
            '12' => ['nullable', new OrganizationRule(parameter: $this->brand)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Noは必須です',
            '0.int' => 'Noは数値である必要があります',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $failedRows = $validator->failed();

            // バリデーションエラーが発生した行番号をキューに追加
            $this->failed($failedRows);
        });
    }

    protected function failed($failedRows)
    {
        // キューにエラー情報を追加するなどの処理を実装
        // 例えば、キューに追加して別のジョブでエラー処理を行うなど
    }

    private function tagImportParam(?array $tags): array
    {
        if (!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if (!isset($tag_name)) continue;
            $tag = ManualTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
            $tags_pram[] = $tag->id;
        }
        return $tags_pram;
    }

    private  function strToArray(?String $str): array
    {
        if (!isset($str)) return [];

        $array = explode(',', $str);

        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[] = trim($value, "\"");
        }

        return $returnArray;
    }

    private function getBrandIdArray(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('id')
            ->toArray();
    }
    private function getBrandNameArray(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('name')
            ->toArray();
    }

    private function getOrg3All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization3.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->pluck('organization3.id')
            ->toArray();
    }

    private function getOrg4All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization4.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->pluck('organization4.id')
            ->toArray();
    }

    private function getOrg5All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization5.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->pluck('organization5.id')
            ->toArray();
    }

    private function targetUserParam($brand): array
    {
        // manual_userに該当のユーザーを登録する
        $target_users_data = [];
        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('brand_id', $brand)->get()->toArray();
        // 該当のユーザー
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();
        foreach ($target_users as $target_user) {
            $target_users_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }
        return $target_users_data;
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }
}
