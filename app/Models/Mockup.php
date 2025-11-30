<?php

namespace App\Models;

use App\Enums\Mockup\TypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'colors',
        'area_top',
        'area_left',
        'area_height',
        'area_width',
    ];

    protected $casts = [
        'colors' => 'array',
        'type' => TypeEnum::class,
    ];
    protected $attributes = [
        'area_width' => 200,
        'area_left' => 233,
        'area_top' => 233,
        'area_height' => 370,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }

    public function templates()
    {
        return $this->belongsToMany(
            Template::class,
            'mockup_template',
            'mockup_id',
            'template_id'
        )
            ->using(MockupTemplate::class)
            ->withPivot('id')
            ->withTimestamps();
    }
    public function show(Request $request)
    {

        $colorSlug = $request->query('color', 'red');
        $hex = $colors[$colorSlug] ?? $colors['red'];

        // 2) مسارات الملفات (عدّلهم لو عندك مكان تاني)
        $basePath    = storage_path('app/01_Base.jpg');   // صورة الشخص بالتيشيرت الرمادي
        $shirtPath   = storage_path('app/03.png');        // التيشيرت فقط PNG شفاف
        $designPath  = storage_path('app/sample.png');    // تصميم تجريبي

        // 3) اقرأ الصور - في v3 بنستخدم read بدل make
        $base   = Image::read($basePath);
        $shirt  = Image::read($shirtPath);
        $design = Image::read($designPath);

        // 4) لو الأحجام مش متطابقة تقدر تظبطها (اختياري لو أنت ضابطهم من المصدر)
        // $shirt->scaleDown(width: $base->width(), height: $base->height());

        // 5) نلوّن التيشيرت حسب الـ HEX
        $tintedShirt = $this->tintShirt($shirt, $hex);

        // 6) نكوّن الـ canvas من الـ base
        $canvas = clone $base;

        // نحط التيشيرت الملون فوق الصورة
        $canvas->place($tintedShirt, 'top-left');

        // 7) نحط التصميم على منطقة الصدر
        $printX = 360;  // من الشمال
        $printY = 660;  // من فوق
        $printW = 480;  // عرض مساحة الطباعة
        $printH = 540;  // ارتفاع مساحة الطباعة

        // خليه يدخل جوّه البوكس مع الحفاظ على الـ aspect ratio
        $design->scaleDown(width: $printW, height: $printH);

        $offsetX = $printX + intval(($printW - $design->width()) / 2);
        $offsetY = $printY;

        $canvas->place($design, 'top-left', $offsetX, $offsetY);

        // 8) تصغير الحجم للويب لو حابب
        $maxDim = (int) $request->query('max_dim', 800);
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        // 9) إخراج الصورة كـ PNG (في v3 مفيش ->response)
        $encoded = $canvas->toPng(); // EncodedImage

        return response((string) $encoded, 200)
            ->header('Content-Type', $encoded->mimetype());

    }

    /**
     * تلوين التيشيرت بناءً على كود HEX مع الحفاظ على الظلال والثنايات
     */
    private function tintShirt(ImageInterface $shirt, string $hex): ImageInterface
    {
        // شيل الـ # لو موجودة
        $hex = ltrim($hex, '#');

        // HEX → RGB (0..255)
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // نحول 0..255 إلى RANGE أهدى شوية -40..40 بدل -100..100
        $map = function (int $c): int {
            return (int) round((($c - 128) / 127) * 40); // -40 .. 40
        };

        $rAdj = $map($r);
        $gAdj = $map($g);
        $bAdj = $map($b);

        // نشتغل على نسخة من صورة التيشيرت
        $img = clone $shirt;

        // نخليها رمادي عشان تفضل فيها الثنايات والـ texture
        $img->greyscale()
            ->colorize($rAdj, $gAdj, $bAdj) // تلوين هادي
            ->contrast(12)                   // شوية كونتراست عشان الثنايات تبان
            ->brightness(-18);                // ننوّرها سنة صغيرة

        return $img;
    }
}
