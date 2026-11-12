<?php

namespace App\Services;

use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\CareArticle;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    /**
     * @return array{items: list<array{id: string, group: string, label: string, hint: string, url: string}>}
     */
    public function search(?User $user, string $query): array
    {
        $query = trim($query);

        return [
            'items' => array_merge(
                $this->actions($user, $query),
                $query === '' ? [] : $this->pets($user, $query),
                $query === '' ? [] : $this->vets($query),
                $query === '' ? [] : $this->products($query),
                $query === '' ? [] : $this->articles($query),
            ),
        ];
    }

    /**
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function actions(?User $user, string $query): array
    {
        $items = [
            $this->item('action-triage', 'Actions', 'Symptom check', 'Safety screen', route('triage')),
            $this->item('action-vets', 'Actions', 'Find a vet', 'Directory', route('vets.index')),
        ];

        if ($user === null) {
            $items[] = $this->item('action-login', 'Actions', 'Log in', 'Account', route('login'));
        } elseif ($user->isOwner()) {
            $items[] = $this->item('action-book', 'Actions', 'Book appointment', 'Visits', route('owner.appointments.create'));
            $items[] = $this->item('action-record', 'Actions', 'Add health record', 'Timeline', $this->healthUrl($user));
            $items[] = $this->item('action-cart', 'Actions', 'Open cart', 'Shop', route('owner.cart.index'));
        } elseif ($user->isVet()) {
            $items[] = $this->item('action-visits', 'Actions', 'Open appointments', 'Clinic', route('vet.appointments'));
            $items[] = $this->item('action-profile', 'Actions', 'Edit clinic profile', 'Hours', route('vet.profile'));
        } elseif ($user->isShelter()) {
            $items[] = $this->item('action-listing', 'Actions', 'Add pet', 'Adoption', route('shelter.listings.create'));
            $items[] = $this->item('action-listings', 'Actions', 'Open listings', 'Shelter', route('shelter.listings'));
        } elseif ($user->isAdmin()) {
            $items[] = $this->item('action-privileges', 'Actions', 'Open role matrix', 'Admin', route('admin.privileges'));
        }

        return $this->matching($items, $query);
    }

    /**
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function pets(?User $user, string $query): array
    {
        $items = [];

        if ($user?->isOwner()) {
            $pets = Pet::query()
                ->where('owner_id', $user->id)
                ->where(fn (Builder $builder) => $this->whereLike($builder, ['name', 'species', 'breed'], $query))
                ->orderBy('name')
                ->limit(5)
                ->get();

            foreach ($pets as $pet) {
                $items[] = $this->item('pet-'.$pet->id, 'Pets', $pet->name, $pet->species, route('owner.pets.show', $pet));
            }
        } elseif ($user?->isVet()) {
            $pets = Pet::query()
                ->whereIn('id', Appointment::query()->where('vet_id', $user->id)->select('pet_id'))
                ->where(fn (Builder $builder) => $this->whereLike($builder, ['name', 'species'], $query))
                ->orderBy('name')
                ->limit(5)
                ->get();

            foreach ($pets as $pet) {
                $items[] = $this->item('patient-'.$pet->id, 'Pets', $pet->name, 'Patient · '.$pet->species, route('vet.pets.history', $pet));
            }
        }

        $listings = AdoptionListing::query()
            ->where(fn (Builder $builder) => $this->whereLike($builder, ['pet_name', 'species'], $query))
            ->orderBy('pet_name')
            ->limit(5)
            ->get();

        foreach ($listings as $listing) {
            $url = $user?->isShelter() && $listing->shelter_id === $user->id
                ? route('shelter.listings.show', $listing)
                : route('adopt.show', $listing);

            $items[] = $this->item('listing-'.$listing->id, 'Pets', $listing->pet_name, 'Adoption · '.$listing->species, $url);
        }

        return array_slice($items, 0, 5);
    }

    /**
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function vets(string $query): array
    {
        return User::query()
            ->where('role', 'vet')
            ->where(fn (Builder $builder) => $this->whereLike($builder, ['name', 'city', 'specialization'], $query))
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (User $vet): array => $this->item(
                'vet-'.$vet->id,
                'Vets',
                $vet->name,
                trim(($vet->specialization ?: 'General practice').' · '.$vet->city),
                route('vets.show', $vet),
            ))
            ->all();
    }

    /**
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function products(string $query): array
    {
        return Product::query()
            ->where('is_active', true)
            ->where(fn (Builder $builder) => $this->whereLike($builder, ['name', 'description'], $query))
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => $this->item(
                'product-'.$product->id,
                'Products',
                $product->name,
                $product->category?->label() ?? 'Shop',
                route('products.show', $product),
            ))
            ->all();
    }

    /**
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function articles(string $query): array
    {
        return CareArticle::query()
            ->where('is_published', true)
            ->where(fn (Builder $builder) => $this->whereLike($builder, ['title', 'summary'], $query))
            ->orderBy('title')
            ->limit(5)
            ->get()
            ->map(fn (CareArticle $article): array => $this->item(
                'article-'.$article->id,
                'Care',
                $article->title,
                $article->category?->label() ?? 'Care',
                route('care.show', $article),
            ))
            ->all();
    }

    private function healthUrl(User $user): string
    {
        $pets = Pet::query()->where('owner_id', $user->id)->orderBy('name')->get();

        if ($pets->count() === 1) {
            return route('owner.pets.health', $pets->first());
        }

        if ($pets->isEmpty()) {
            return route('owner.pets.create');
        }

        return route('owner.pets.index');
    }

    /**
     * @param  list<array{id: string, group: string, label: string, hint: string, url: string}>  $items
     * @return list<array{id: string, group: string, label: string, hint: string, url: string}>
     */
    private function matching(array $items, string $query): array
    {
        if ($query === '') {
            return $items;
        }

        $needle = mb_strtolower($query);

        return array_values(array_filter(
            $items,
            fn (array $item): bool => str_contains(mb_strtolower($item['label'].' '.$item['hint']), $needle)
        ));
    }

    /**
     * @param  list<string>  $columns
     */
    private function whereLike(Builder $builder, array $columns, string $query): void
    {
        $like = '%'.addcslashes($query, '%_\\').'%';

        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $builder->{$method}($column, 'like', $like);
        }
    }

    /**
     * @return array{id: string, group: string, label: string, hint: string, url: string}
     */
    private function item(string $id, string $group, string $label, string $hint, string $url): array
    {
        return [
            'id' => $id,
            'group' => $group,
            'label' => $label,
            'hint' => $hint,
            'url' => $url,
        ];
    }
}
