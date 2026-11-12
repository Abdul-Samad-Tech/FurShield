<?php

namespace Database\Seeders;

use App\Enums\AdoptionStatus;
use App\Enums\AppointmentStatus;
use App\Enums\CareCategory;
use App\Enums\OrderStatus;
use App\Enums\PrivilegeKey;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\CareArticle;
use App\Models\HealthRecord;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pet;
use App\Models\Product;
use App\Models\RolePrivilege;
use App\Models\ShelterCareLog;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Services\NotificationService;
use App\Services\PetIdService;
use App\Support\DummyImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $verified = Carbon::now('UTC');

        User::query()->create([
            'name' => 'FurShield Admin',
            'email' => 'admin@furshield.test',
            'password' => $password,
            'role' => UserRole::Admin,
            'phone' => '+92-300-0000001',
            'address' => 'FurShield HQ, Karachi',
            'city' => 'Karachi',
            'email_verified_at' => $verified,
        ]);

        $ayesha = $this->owner('Ayesha Khan', 'owner@furshield.test', '+92-300-1112233', '12 Maple Street, Karachi', 'Karachi', $password, $verified);
        $bilal = $this->owner('Bilal Ahmed', 'owner.bilal@furshield.test', '+92-300-2223344', '8 Garden Town, Lahore', 'Lahore', $password, $verified);
        $fatima = $this->owner('Fatima Noor', 'owner.fatima@furshield.test', '+92-300-3334455', '19 F-7, Islamabad', 'Islamabad', $password, $verified);

        $hassan = User::query()->create([
            'name' => 'Dr. Hassan Ali',
            'email' => 'vet@furshield.test',
            'password' => $password,
            'role' => UserRole::Vet,
            'phone' => '+92-300-4455667',
            'address' => 'Clinic Road, Lahore',
            'city' => 'Lahore',
            'latitude' => 31.5204000,
            'longitude' => 74.3587000,
            'specialization' => 'Small Animal Medicine',
            'experience_years' => 8,
            'bio' => 'Focused on preventive care, vaccinations, and feline health.',
            'available_slots' => [
                ['weekday' => 1, 'start' => '09:00', 'end' => '13:00', 'slot_minutes' => 30],
                ['weekday' => 3, 'start' => '14:00', 'end' => '17:00', 'slot_minutes' => 30],
                ['weekday' => 5, 'start' => '10:00', 'end' => '13:00', 'slot_minutes' => 30],
            ],
            'email_verified_at' => $verified,
        ]);

        $pawHaven = User::query()->create([
            'name' => 'Paw Haven Shelter',
            'email' => 'shelter@furshield.test',
            'password' => $password,
            'role' => UserRole::Shelter,
            'phone' => '+92-300-7788990',
            'address' => '45 Green Belt, Islamabad',
            'city' => 'Islamabad',
            'latitude' => 33.7100000,
            'longitude' => 73.0551000,
            'shelter_name' => 'Paw Haven Shelter',
            'contact_person' => 'Sara Malik',
            'email_verified_at' => $verified,
        ]);

        $milo = $this->pet($ayesha, 'Milo', 'Dog', 'Beagle', 3, 'Male', 'Fully vaccinated. Mild pollen allergy noted on the record.', 0, true);
        $luna = $this->pet($ayesha, 'Luna', 'Cat', 'Persian', 2, 'Female', 'Indoor cat. Annual checkup is on the timeline.', 1);
        $pip = $this->pet($ayesha, 'Pip', 'Bird', 'Budgerigar', 1, 'Male', 'Wing clip not requested. Eats a seed mix.', 2);
        $coco = $this->pet($bilal, 'Coco', 'Dog', 'Indie mix', 4, 'Female', 'Walks twice a day. No known allergies.', 3);
        $nori = $this->pet($bilal, 'Nori', 'Cat', 'Domestic shorthair', 1, 'Male', 'Indoor cat. Neutered.', 0);
        $bruno = $this->pet($fatima, 'Bruno', 'Dog', 'Labrador', 6, 'Male', 'Joint stiffness after long walks.', 1);
        $kiki = $this->pet($fatima, 'Kiki', 'Rabbit', 'Holland Lop', 2, 'Female', 'Hay-based diet. Nails trimmed last month.', 2);
        $olive = $this->pet($fatima, 'Olive', 'Cat', 'British shorthair', 5, 'Female', 'Indoor cat. Dental cleaning discussed.', 3);

        $this->record($milo, $hassan, Carbon::today()->subMonths(2), Carbon::today()->addDays(3), 365, 'Rabies booster', 'Routine vaccination is on the timeline.');
        $this->record($luna, $hassan, Carbon::today()->subMonths(6), Carbon::today()->subDays(2), 180, 'Flea treatment', 'The previous dose is past its due date.');
        $this->record($bruno, $hassan, Carbon::today()->subYear(), Carbon::today()->addDay(), 365, 'Dental check', 'The next dental check is tomorrow.');
        $this->record($coco, $hassan, Carbon::today()->subMonths(1), Carbon::today()->addDays(40), null, 'Annual exam', 'The next exam is outside the reminder window.');

        $products = [
            ['Premium Salmon Kibble', ProductCategory::Food, 42.50, 'Grain-free adult dog food with omega oils.', 40],
            ['Gentle Coat Shampoo', ProductCategory::Grooming, 18.00, 'Hypoallergenic shampoo for sensitive skin.', 55],
            ['Feather Wand Toy', ProductCategory::Toys, 9.99, 'Interactive toy for indoor cats.', 80],
            ['Joint Support Chews', ProductCategory::Health, 27.50, 'Daily soft chews for mobility support.', 35],
            ['Reflective Walking Harness', ProductCategory::Accessories, 32.00, 'Adjustable harness with night visibility.', 28],
            ['Clicker Training Kit', ProductCategory::Training, 14.75, 'Beginner positive-reinforcement kit.', 59],
        ];

        $catalog = [];
        foreach ($products as $index => [$name, $category, $price, $description, $stock]) {
            $catalog[$name] = Product::query()->create([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'description' => $description,
                'stock_quantity' => $stock,
                'image_path' => DummyImages::url('product', $index),
                'is_active' => true,
                'average_rating' => 4.5,
            ]);
        }

        $articles = [
            ['Balanced Feeding Basics', CareCategory::Feeding, 'How to portion meals for dogs and cats by age and activity.'],
            ['Weekly Hygiene Ritual', CareCategory::Hygiene, 'Grooming, nail care, and dental routines that prevent common issues.'],
            ['Daily Exercise Ideas', CareCategory::Exercise, 'Indoor and outdoor movement plans for busy pet parents.'],
            ['Positive Training Tips', CareCategory::Training, 'Short sessions that build trust without stress.'],
            ['When to Call a Vet', CareCategory::Health, 'Warning signs that need professional attention quickly.'],
            ['Adoption FAQs', CareCategory::Faq, 'Common questions from first-time adopters answered clearly.'],
        ];

        foreach ($articles as $index => [$title, $category, $summary]) {
            CareArticle::query()->create([
                'title' => $title,
                'category' => $category,
                'content_type' => $category === CareCategory::Faq ? 'faq' : 'article',
                'summary' => $summary,
                'body' => $summary.' This is sample guidance stored with FurShield so the care library has something to read. It is not a veterinary prescription.',
                'image_path' => DummyImages::url('care', $index),
                'video_url' => null,
                'is_published' => true,
            ]);
        }

        $buddy = $this->listing($pawHaven, 'Buddy', 'Dog', 'Indie Mix', 1, 'Male', 'Vaccinated and dewormed', 'Playful and friendly with kids. Looking for an active home.', AdoptionStatus::Available, 0);
        $this->listing($pawHaven, 'Mochi', 'Cat', 'Domestic Shorthair', 2, 'Female', 'Spayed, healthy appetite', 'Calm lap cat who loves sunny windowsills.', AdoptionStatus::Available, 1);
        $fig = $this->listing($pawHaven, 'Fig', 'Cat', 'Tabby', 3, 'Male', 'Neutered', 'Quiet adult cat. Sample listing held while an application is reviewed.', AdoptionStatus::Pending, 2);

        ShelterCareLog::query()->create([
            'listing_id' => $buddy->id,
            'care_type' => 'Feeding',
            'notes' => 'Sample care note: two meals yesterday, ate both.',
            'logged_at' => Carbon::now('UTC')->subDay(),
        ]);

        $this->call(DemoPlaceSeeder::class);

        $creek = User::query()->where('email', 'shelter.creek@furshield.test')->firstOrFail();
        $this->listing($creek, 'Pebble', 'Dog', 'Terrier mix', 2, 'Female', 'Vaccinated', 'Sample listing for a small dog who prefers a quiet home.', AdoptionStatus::Available, 3);
        $this->listing($creek, 'Ash', 'Dog', 'Hound mix', 4, 'Male', 'Adopted from the sample board', 'Sample listing kept so the adopted status is visible.', AdoptionStatus::Adopted, 0);

        AdoptionInterest::query()->create([
            'listing_id' => $buddy->id,
            'owner_id' => $fatima->id,
            'message' => 'Sample interest from Fatima. This is not a real application.',
            'status' => 'pending',
        ]);
        AdoptionInterest::query()->create([
            'listing_id' => $fig->id,
            'owner_id' => $bilal->id,
            'message' => 'Sample interest that the shelter has already accepted.',
            'status' => 'accepted',
            'shelter_response' => 'Sample reply: we can arrange a visit.',
        ]);

        $noor = User::query()->where('email', 'vet.noor@furshield.test')->firstOrFail();
        $omar = User::query()->where('email', 'vet.omar@furshield.test')->firstOrFail();
        $hina = User::query()->where('email', 'vet.hina@furshield.test')->firstOrFail();

        $this->visit($milo, $ayesha, $hassan, Carbon::now()->addDays(3)->setTime(10, 30), AppointmentStatus::Pending, 'Seasonal allergy check', 'Scratching more after outdoor walks.');
        $this->visit($coco, $bilal, $noor, Carbon::now()->addDays(5)->setTime(11, 0), AppointmentStatus::Approved, 'Spay follow-up', 'The incision looks quiet.');
        $this->visit($bruno, $fatima, $omar, Carbon::now()->addDays(6)->setTime(15, 0), AppointmentStatus::Rescheduled, 'Itchy coat', 'Moved from the morning clinic.');
        $this->visit($luna, $ayesha, $hassan, Carbon::now()->subDays(12)->setTime(9, 30), AppointmentStatus::Completed, 'Wellness exam', 'Completed in the sample history.');
        $this->visit($nori, $bilal, $hina, Carbon::now()->subDays(4)->setTime(11, 30), AppointmentStatus::Cancelled, 'Nail trim', 'The owner cancelled this sample visit.');
        $this->visit($pip, $ayesha, $hina, Carbon::now()->addDays(8)->setTime(12, 0), AppointmentStatus::Pending, 'Feather check', 'First visit for Pip.');
        $this->visit($kiki, $fatima, $omar, Carbon::now()->addDays(2)->setTime(10, 0), AppointmentStatus::Approved, 'Appetite check', 'Eating less hay this week.');

        $order = Order::query()->create([
            'owner_id' => $ayesha->id,
            'total_amount' => 14.75,
            'status' => OrderStatus::Placed,
            'order_date' => Carbon::now('UTC')->subDays(2),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $catalog['Clicker Training Kit']->id,
            'quantity' => 1,
            'price_each' => 14.75,
        ]);

        $notifier = app(NotificationService::class);
        $notifier->notify($ayesha, 'Milo is due for care in 3 days', 'Rabies booster is on the sample timeline. Open Milo to review it.', 'reminder', '/owner/pets');
        $notifier->notify($ayesha, 'Luna is overdue for care', 'Flea treatment is past the sample due date.', 'reminder', '/owner/pets');
        $notifier->notify($ayesha, 'Visit requested', 'Milo has a pending sample visit with Dr. Hassan Ali.', 'appointment', '/owner/appointments');
        $notifier->notify($ayesha, 'Order placed', 'Sample order for the Clicker Training Kit. Payment and delivery are not part of FurShield.', 'order', '/owner/orders');
        $notifier->notify($bilal, 'Visit approved', 'Coco’s sample follow-up with Dr. Noor Fatima is approved.', 'appointment', '/owner/appointments');
        $notifier->notify($fatima, 'Visit moved', 'Bruno’s sample dermatology visit was rescheduled.', 'appointment', '/owner/appointments');
        $notifier->notify($fatima, 'Bruno is due for care tomorrow', 'Dental check is on tomorrow’s sample timeline.', 'reminder', '/owner/pets');
        $notifier->notify($hassan, 'New appointment request', 'Ayesha Khan requested a sample visit for Milo.', 'appointment', '/vet/appointments');
        $notifier->notify($pawHaven, 'New adoption interest', 'Fatima Noor sent sample interest in Buddy.', 'adoption', '/shelter/listings');
        $notifier->notify($creek, 'Listing published', 'Pebble is on the sample adoption board.', 'adoption', '/shelter/listings');

        $this->seedPrivileges();
    }

    private function owner(string $name, string $email, string $phone, string $address, string $city, string $password, Carbon $verified): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Owner,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'email_verified_at' => $verified,
        ]);
    }

    private function pet(User $owner, string $name, string $species, string $breed, int $age, string $gender, string $history, int $photo, bool $shareIdentity = false): Pet
    {
        $fields = PetIdService::defaults();
        if ($shareIdentity) {
            $fields['name'] = true;
            $fields['species'] = true;
        }

        return Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'species' => $species,
            'breed' => $breed,
            'age' => $age,
            'gender' => $gender,
            'medical_history' => $history,
            'photo_path' => DummyImages::url('pet', $photo),
            'gallery' => [],
            'public_fields' => $fields,
        ]);
    }

    private function record(Pet $pet, User $vet, Carbon $visit, Carbon $due, ?int $every, string $title, string $notes): void
    {
        HealthRecord::query()->create([
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'visit_date' => $visit->toDateString(),
            'next_due_date' => $due->toDateString(),
            'recurrence_days' => $every,
            'record_type' => 'vaccination',
            'title' => $title,
            'diagnosis' => 'Sample record',
            'treatment' => $title,
            'notes' => $notes,
        ]);
    }

    private function listing(User $shelter, string $name, string $species, string $breed, int $age, string $gender, string $health, string $description, AdoptionStatus $status, int $photo): AdoptionListing
    {
        return AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => $name,
            'species' => $species,
            'breed' => $breed,
            'age' => $age,
            'gender' => $gender,
            'health_status' => $health,
            'description' => $description,
            'photo_path' => DummyImages::url('adoption', $photo),
            'status' => $status,
        ]);
    }

    private function visit(Pet $pet, User $owner, User $vet, Carbon $when, AppointmentStatus $status, string $reason, string $notes): void
    {
        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => $when,
            'status' => $status,
            'reason' => $reason,
            'owner_notes' => $notes,
        ]);
    }

    private function seedPrivileges(): void
    {
        $defaults = [
            UserRole::Owner->value => [
                PrivilegeKey::ManagePets,
                PrivilegeKey::ManageHealth,
                PrivilegeKey::BookAppointments,
                PrivilegeKey::ShopProducts,
                PrivilegeKey::ManageCart,
                PrivilegeKey::BrowseCareTips,
                PrivilegeKey::BrowseAdoptions,
            ],
            UserRole::Vet->value => [
                PrivilegeKey::ManageAppointments,
                PrivilegeKey::LogTreatments,
                PrivilegeKey::ViewMedicalHistory,
                PrivilegeKey::BrowseCareTips,
            ],
            UserRole::Shelter->value => [
                PrivilegeKey::ManageListings,
                PrivilegeKey::ManageCareLogs,
                PrivilegeKey::RespondInterests,
                PrivilegeKey::BrowseCareTips,
                PrivilegeKey::BrowseAdoptions,
            ],
        ];

        foreach (UserRole::cases() as $role) {
            if ($role === UserRole::Admin) {
                continue;
            }

            foreach (PrivilegeKey::cases() as $privilege) {
                $allowed = in_array($privilege, $defaults[$role->value] ?? [], true);

                RolePrivilege::query()->create([
                    'role' => $role->value,
                    'privilege_key' => $privilege->value,
                    'is_allowed' => $allowed,
                ]);
            }
        }

        User::query()
            ->where('role', '!=', UserRole::Admin->value)
            ->get()
            ->each(function (User $user) use ($defaults): void {
                foreach (PrivilegeKey::cases() as $privilege) {
                    $allowed = in_array($privilege, $defaults[$user->role->value] ?? [], true);

                    UserPrivilege::query()->create([
                        'user_id' => $user->id,
                        'privilege_key' => $privilege->value,
                        'is_allowed' => $allowed,
                    ]);
                }
            });
    }
}
