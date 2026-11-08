<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * Flat vector illustration of a household appliance, chosen from its name or category.
 */
class ApplianceArt extends Component
{
    /**
     * Name keywords checked in order (more specific first) => illustration type.
     *
     * @var array<string, string>
     */
    public const NAME_KEYWORDS = [
        'kulkas' => 'fridge',
        'freezer' => 'fridge',
        'lemari es' => 'fridge',
        'mesin cuci' => 'washer',
        'setrika' => 'iron',
        'rice cooker' => 'rice_cooker',
        'magic com' => 'rice_cooker',
        'penanak' => 'rice_cooker',
        'microwave' => 'microwave',
        'oven' => 'microwave',
        'air fryer' => 'microwave',
        'pemanas' => 'heater',
        'water heater' => 'heater',
        'pompa' => 'pump',
        'router' => 'router',
        'wi-fi' => 'router',
        'wifi' => 'router',
        'modem' => 'router',
        'kipas' => 'fan',
        'televisi' => 'tv',
        'smart tv' => 'tv',
        'tv ' => 'tv',
        'monitor' => 'tv',
        'komputer' => 'tv',
        'lampu' => 'lamp',
        'ac ' => 'ac',
        'pendingin' => 'ac',
    ];

    /**
     * Appliance category code => fallback illustration type.
     *
     * @var array<string, string>
     */
    public const CATEGORY_TYPES = [
        'cooling' => 'ac',
        'refrigeration' => 'fridge',
        'lighting' => 'lamp',
        'entertainment' => 'tv',
        'kitchen' => 'rice_cooker',
        'laundry' => 'washer',
        'water_pump' => 'pump',
    ];

    /**
     * Illustration type => file name (without extension) of an optional default photo
     * in public/images/appliances, e.g. public/images/appliances/kulkas.jpg.
     *
     * @var array<string, string>
     */
    public const DEFAULT_PHOTO_NAMES = [
        'ac' => 'ac',
        'fridge' => 'kulkas',
        'washer' => 'mesin-cuci',
        'tv' => 'tv',
        'lamp' => 'lampu',
        'rice_cooker' => 'rice-cooker',
        'pump' => 'pompa-air',
        'iron' => 'setrika',
        'fan' => 'kipas',
        'router' => 'router',
        'heater' => 'pemanas-air',
        'microwave' => 'microwave',
        'plug' => 'lainnya',
    ];

    public const PHOTO_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

    /**
     * @var array<string, string|null>
     */
    protected static array $defaultPhotoCache = [];

    public string $type;

    /**
     * Photo shown instead of the illustration: the appliance's own upload, else a default photo for its type.
     */
    public ?string $photoUrl;

    public function __construct(
        public ?string $name = null,
        public ?string $category = null,
        ?string $type = null,
        ?string $photo = null,
        public string $class = 'size-12',
    ) {
        $this->type = $type ?? self::typeFor($name, $category);
        $this->photoUrl = $photo ?? self::defaultPhotoFor($this->type);
    }

    /**
     * URL of the default photo for an illustration type, if one was placed in public/images/appliances.
     */
    public static function defaultPhotoFor(string $type): ?string
    {
        if (array_key_exists($type, self::$defaultPhotoCache)) {
            return self::$defaultPhotoCache[$type];
        }

        $fileName = self::DEFAULT_PHOTO_NAMES[$type] ?? $type;
        foreach (self::PHOTO_EXTENSIONS as $extension) {
            $relativePath = "images/appliances/{$fileName}.{$extension}";
            if (is_file(public_path($relativePath))) {
                return self::$defaultPhotoCache[$type] = asset($relativePath).'?v='.filemtime(public_path($relativePath));
            }
        }

        return self::$defaultPhotoCache[$type] = null;
    }

    public static function flushDefaultPhotoCache(): void
    {
        self::$defaultPhotoCache = [];
    }

    /**
     * Resolve the illustration type from an appliance name, falling back to its category code.
     */
    public static function typeFor(?string $name, ?string $categoryCode = null): string
    {
        $haystack = ' '.Str::lower((string) $name).' ';

        foreach (self::NAME_KEYWORDS as $keyword => $type) {
            if (str_contains($haystack, ' '.$keyword) || str_contains($haystack, $keyword.' ')) {
                return $type;
            }
        }

        return self::CATEGORY_TYPES[$categoryCode] ?? 'plug';
    }

    public function render(): View
    {
        return view('components.appliance-art');
    }
}
