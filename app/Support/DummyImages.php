<?php

namespace App\Support;

final class DummyImages
{
    /**
     * Local copies of the former Unsplash catalog.
     * photo-1623387642240-6dbba5274119 now returns 404, so that id uses the outdoor dog photo.
     *
     * @var array<string, array{file: string, width: int, height: int, alt: string}>
     */
    private const IMAGES = [
        '1589924691995-400dc9ecc119' => [
            'file' => 'images/catalog-bowls.jpg',
            'width' => 1200,
            'height' => 800,
            'alt' => 'Kibble in a blue bowl beside a dog\'s paws on a wood floor',
        ],
        '1516734212186-a967f81ad0d7' => [
            'file' => 'images/catalog-treat.jpg',
            'width' => 1200,
            'height' => 800,
            'alt' => 'A golden dog being bathed',
        ],
        '1544568100-847a948585b9' => [
            'file' => 'images/catalog-portrait.jpg',
            'width' => 1200,
            'height' => 900,
            'alt' => 'A brown dog standing in tall grass',
        ],
        '1601758228041-f3b2795255f1' => [
            'file' => 'images/catalog-companion.jpg',
            'width' => 1200,
            'height' => 1798,
            'alt' => 'A brindle dog chewing a plush toy on a wood floor',
        ],
        '1623387642240-6dbba5274119' => [
            'file' => 'images/catalog-walk.jpg',
            'width' => 1200,
            'height' => 1798,
            'alt' => 'A white dog holding a blue flying disc outdoors',
        ],
        '1587300003388-59208cc962cb' => [
            'file' => 'images/catalog-dog-face.jpg',
            'width' => 1200,
            'height' => 800,
            'alt' => 'A brown and white dog on a beach',
        ],
        '1558788353-f76d92427f16' => [
            'file' => 'images/catalog-puppy.jpg',
            'width' => 1200,
            'height' => 1590,
            'alt' => 'A golden dog looking at the camera',
        ],
        '1574158622682-e40e69881006' => [
            'file' => 'images/catalog-cat-eyes.jpg',
            'width' => 1200,
            'height' => 1200,
            'alt' => 'A tabby cat looking up against a blue sky',
        ],
        '1583511655857-d19b40a7a54e' => [
            'file' => 'images/catalog-dog-smile.jpg',
            'width' => 1200,
            'height' => 801,
            'alt' => 'A French bulldog in a yellow shirt sitting on a blue floor',
        ],
        '1514888286974-6c03e2ca1dba' => [
            'file' => 'images/catalog-cat-sit.jpg',
            'width' => 1200,
            'height' => 825,
            'alt' => 'A black and white cat resting its paws on bamboo',
        ],
        '1548199973-03cce0bbc87b' => [
            'file' => 'images/catalog-two-dogs.jpg',
            'width' => 1200,
            'height' => 801,
            'alt' => 'A corgi and a small terrier running on a dirt path',
        ],
        '1450778869180-41d0601e046e' => [
            'file' => 'images/catalog-couch.jpg',
            'width' => 1200,
            'height' => 706,
            'alt' => 'A cat resting its head on a dog lying in greenery',
        ],
        '1583337130417-3346a1be7dee' => [
            'file' => 'images/catalog-bandana.jpg',
            'width' => 1200,
            'height' => 1500,
            'alt' => 'A French bulldog in a yellow hoodie looking over its shoulder',
        ],
        '1530281700549-e82e7bf110d6' => [
            'file' => 'images/catalog-outdoors.jpg',
            'width' => 1200,
            'height' => 1797,
            'alt' => 'A brown dog running through shallow sea water',
        ],
        '1598133894008-61f7fdb8cc3a' => [
            'file' => 'images/catalog-terrier.jpg',
            'width' => 1200,
            'height' => 1797,
            'alt' => 'A French bulldog in a green jacket against a green background',
        ],
        '1601758125946-6ec2ef64daf8' => [
            'file' => 'images/catalog-walk.jpg',
            'width' => 1200,
            'height' => 1798,
            'alt' => 'A white dog holding a blue flying disc outdoors',
        ],
        '1552053831-71594a27632d' => [
            'file' => 'images/catalog-retriever.jpg',
            'width' => 1200,
            'height' => 1924,
            'alt' => 'A golden puppy sitting on a patio with a tulip',
        ],
        '1518791841217-8f162f1e1131' => [
            'file' => 'images/catalog-tabby.jpg',
            'width' => 1200,
            'height' => 800,
            'alt' => 'A tabby cat lying on a sofa',
        ],
        '1573865526739-10659fec78a5' => [
            'file' => 'images/catalog-kitten.jpg',
            'width' => 1200,
            'height' => 1680,
            'alt' => 'An orange cat lying on a wooden table',
        ],
        '1559839734-2b71ea197ec2' => [
            'file' => 'images/catalog-clinician.jpg',
            'width' => 1200,
            'height' => 800,
            'alt' => 'A clinician in a white coat standing outdoors',
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const CATALOG = [
        'product' => [
            '1589924691995-400dc9ecc119',
            '1516734212186-a967f81ad0d7',
            '1544568100-847a948585b9',
            '1601758228041-f3b2795255f1',
            '1623387642240-6dbba5274119',
            '1587300003388-59208cc962cb',
        ],
        'adoption' => [
            '1558788353-f76d92427f16',
            '1574158622682-e40e69881006',
            '1583511655857-d19b40a7a54e',
            '1514888286974-6c03e2ca1dba',
        ],
        'care' => [
            '1548199973-03cce0bbc87b',
            '1450778869180-41d0601e046e',
            '1583337130417-3346a1be7dee',
            '1530281700549-e82e7bf110d6',
            '1598133894008-61f7fdb8cc3a',
            '1601758125946-6ec2ef64daf8',
        ],
        'pet' => [
            '1552053831-71594a27632d',
            '1518791841217-8f162f1e1131',
            '1587300003388-59208cc962cb',
            '1573865526739-10659fec78a5',
        ],
        'role' => [
            '1601758228041-f3b2795255f1',
            '1559839734-2b71ea197ec2',
            '1450778869180-41d0601e046e',
        ],
    ];

    public static function url(string $type, int $seed = 0): string
    {
        return asset(self::entry($type, $seed)['file']);
    }

    /**
     * @return array{url: string, width: int|null, height: int|null, alt: string}
     */
    public static function describe(?string $path, string $type, int $seed = 0): array
    {
        $matched = self::match($path);

        if ($matched !== null) {
            return [
                'url' => asset($matched['file']),
                'width' => $matched['width'],
                'height' => $matched['height'],
                'alt' => $matched['alt'],
            ];
        }

        if ($path !== null && $path !== '' && ! self::isRemote($path)) {
            $full = public_path('storage/'.$path);
            $size = is_file($full) ? getimagesize($full) : false;

            return [
                'url' => asset('storage/'.$path),
                'width' => $size[0] ?? null,
                'height' => $size[1] ?? null,
                'alt' => 'Pet photo',
            ];
        }

        $entry = self::entry($type, $seed);

        return [
            'url' => asset($entry['file']),
            'width' => $entry['width'],
            'height' => $entry['height'],
            'alt' => $entry['alt'],
        ];
    }

    public static function resolve(?string $path, string $type, int $seed = 0): string
    {
        return self::describe($path, $type, $seed)['url'];
    }

    /**
     * @return array{file: string, width: int, height: int, alt: string}
     */
    private static function entry(string $type, int $seed): array
    {
        $pool = self::CATALOG[$type] ?? self::CATALOG['pet'];
        $id = $pool[abs($seed) % count($pool)];

        return self::IMAGES[$id];
    }

    /**
     * @return array{file: string, width: int, height: int, alt: string}|null
     */
    private static function match(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (preg_match('/photo-([0-9]+-[A-Za-z0-9_-]+)/', $path, $matches) === 1 && isset(self::IMAGES[$matches[1]])) {
            return self::IMAGES[$matches[1]];
        }

        foreach (self::IMAGES as $image) {
            if (str_ends_with($path, $image['file'])) {
                return $image;
            }
        }

        return null;
    }

    private static function isRemote(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
