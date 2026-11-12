<?php

namespace App\Enums;

enum PrivilegeKey: string
{
    case ManagePets = 'manage_pets';
    case ManageHealth = 'manage_health';
    case BookAppointments = 'book_appointments';
    case ManageAppointments = 'manage_appointments';
    case LogTreatments = 'log_treatments';
    case ViewMedicalHistory = 'view_medical_history';
    case ShopProducts = 'shop_products';
    case ManageCart = 'manage_cart';
    case BrowseCareTips = 'browse_care_tips';
    case BrowseAdoptions = 'browse_adoptions';
    case ManageListings = 'manage_listings';
    case ManageCareLogs = 'manage_care_logs';
    case RespondInterests = 'respond_interests';
    case ManageUsers = 'manage_users';
    case ManagePrivileges = 'manage_privileges';
    case ViewReports = 'view_reports';

    public function label(): string
    {
        return match ($this) {
            self::ManagePets => 'Manage Pets',
            self::ManageHealth => 'Manage Health Records',
            self::BookAppointments => 'Book Appointments',
            self::ManageAppointments => 'Manage Appointments',
            self::LogTreatments => 'Log Treatments',
            self::ViewMedicalHistory => 'View Medical History',
            self::ShopProducts => 'Shop Products',
            self::ManageCart => 'Manage Cart / Orders',
            self::BrowseCareTips => 'Browse Care Tips',
            self::BrowseAdoptions => 'Browse Adoptions',
            self::ManageListings => 'Manage Adoption Listings',
            self::ManageCareLogs => 'Manage Shelter Care Logs',
            self::RespondInterests => 'Respond to Adopter Interests',
            self::ManageUsers => 'Manage Users',
            self::ManagePrivileges => 'Manage Privileges',
            self::ViewReports => 'View Reports',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
