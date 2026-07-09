<?php
$tags = \App\Models\Tag::all();
\App\Models\MenuItem::all()->each(function($item) use ($tags) {
    if ($tags->count() > 0) {
        $item->tags()->sync($tags->random(rand(1, 3))->pluck('id'));
    }
    $item->update([
        'Protein' => rand(10, 50),
        'Carbs' => rand(20, 100),
        'Fats' => rand(5, 30)
    ]);
});
echo "Done!\n";
