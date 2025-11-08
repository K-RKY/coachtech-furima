<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fashion      = Category::where('name', 'ファッション')->first();
        $electronics  = Category::where('name', '家電')->first();
        $interior     = Category::where('name', 'インテリア')->first();
        $men          = Category::where('name', 'メンズ')->first();
        $cosmetics    = Category::where('name', 'コスメ')->first();
        $book         = Category::where('name', '本')->first();
        $game         = Category::where('name', 'ゲーム')->first();
        $sports       = Category::where('name', 'スポーツ')->first();
        $kitchen      = Category::where('name', 'キッチン')->first();
        $handmade     = Category::where('name', 'ハンドメイド')->first();
        $accessory    = Category::where('name', 'アクセサリー')->first();
        $toy          = Category::where('name', 'おもちゃ')->first();
        $babyKids     = Category::where('name', 'ベビー・キッズ')->first();

        $yamada   = User::where('email', 'yamada@test.com')->first();
        $suzuki   = User::where('email', 'suzuki@test.com')->first();
        $sato     = User::where('email', 'sato@test.com')->first();
        $tanaka   = User::where('email', 'tanaka@test.com')->first();
        $kobayashi = User::where('email', 'kobayashi@test.com')->first();

        $item1 = Item::create([
            'user_id' => $yamada->id,
            'name' => '腕時計',
            'brand' => 'Rolax',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'condition_id' => 1,
            'price' => 15000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
        ]);
        $item1->categories()->attach([$accessory->id, $men->id]);

        $item2 = Item::create([
            'user_id' => $yamada->id,
            'name' => 'HDD',
            'brand' => '西芝',
            'description' => '高速で信頼性の高いハードディスク',
            'condition_id' => 2,
            'price' => 5000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
        ]);
        $item2->categories()->attach([$electronics->id]);

        $item3 = Item::create([
            'user_id' => $suzuki->id,
            'name' => '玉ねぎ3束',
            'brand' => 'なし',
            'description' => '新鮮な玉ねぎ3束のセット',
            'condition_id' => 3,
            'price' => 300,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
        ]);
        $item3->categories()->attach([$kitchen->id]);

        $item4 = Item::create([
            'user_id' => $suzuki->id,
            'name' => '革靴',
            'brand' => '',
            'description' => 'クラシックなデザインの革靴',
            'condition_id' => 4,
            'price' => 4000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
        ]);
        $item4->categories()->attach([$fashion->id]);

        $item5 = Item::create([
            'user_id' => $sato->id,
            'name' => 'ノートPC',
            'brand' => '',
            'description' => '高性能なノートパソコン',
            'condition_id' => 1,
            'price' => 45000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
        ]);
        $item5->categories()->attach([$electronics->id]);

        $item6 = Item::create([
            'user_id' => $sato->id,
            'name' => 'マイク',
            'brand' => 'なし',
            'description' => '高音質のレコーディング用マイク',
            'condition_id' => 2,
            'price' => 8000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
        ]);
        $item6->categories()->attach([$electronics->id]);

        $item7 = Item::create([
            'user_id' => $tanaka->id,
            'name' => 'ショルダーバッグ',
            'brand' => '',
            'description' => 'おしゃれなショルダーバッグ',
            'condition_id' => 3,
            'price' => 3500,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
        ]);
        $item7->categories()->attach([$fashion->id]);

        $item8 = Item::create([
            'user_id' => $tanaka->id,
            'name' => 'タンブラー',
            'brand' => 'なし',
            'description' => '使いやすいタンブラー',
            'condition_id' => 4,
            'price' => 500,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
        ]);
        $item8->categories()->attach([$kitchen->id]);

        $item9 = Item::create([
            'user_id' => $kobayashi->id,
            'name' => 'コーヒーミル',
            'brand' => 'Starbacks',
            'description' => '手動のコーヒーミル',
            'condition_id' => 1,
            'price' => 4000,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
        ]);
        $item9->categories()->attach([$kitchen->id]);

        $item10 = Item::create([
            'user_id' => $kobayashi->id,
            'name' => 'メイクセット',
            'brand' => '',
            'description' => '便利なメイクアップセット',
            'condition_id' => 2,
            'price' => 2500,
            'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
        ]);
        $item10->categories()->attach([$cosmetics->id]);
    }
}
