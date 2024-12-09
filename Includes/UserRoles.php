<?php
namespace FourDash\Includes;
/**
 * User Roles
 * /includes/class-fourdash-user-roles.php
 * @package fourdash
 * @subpackage Includes
 * @since 1.0.0
 */

if (!defined('WPINC')) {
    die;
}

class User_Roles {
    public function __construct() {
        add_action('init', array($this, 'add_custom_roles'));
        add_action('admin_init', array($this, 'add_role_capabilities'));
    }

    public function add_custom_roles() {
        add_role('fourdash_manager', 'Manager', array());
        add_role('fourdash_assistant_manager', 'Assistant Manager', array());
        add_role('fourdash_employee', 'Employee', array());
    }

    public function add_role_capabilities() {
        $manager = get_role('fourdash_manager');
        $assistant_manager = get_role('fourdash_assistant_manager');
        $employee = get_role('fourdash_employee');

        if ($manager) {
            $manager->add_cap('manage_fourdash');
            $manager->add_cap('edit_fourdash_settings');
            $manager->add_cap('view_all_fourdash_calendars');
            $manager->add_cap('edit_fourdash_employees');
        }

        if ($assistant_manager) {
            $assistant_manager->add_cap('manage_fourdash');
            $assistant_manager->add_cap('view_all_fourdash_calendars');
            $assistant_manager->add_cap('edit_fourdash_employees');
        }

        if ($employee) {
            $employee->add_cap('edit_own_fourdash_calendar');
            $employee->add_cap('view_own_fourdash_calendar');
        }
    }

    public function get_user_role($user_id) {
        $user = get_userdata($user_id);
        if ($user && $user->roles) {
            return array_intersect(['fourdash_manager', 'fourdash_assistant_manager', 'fourdash_employee'], $user->roles);
        }
        return array();
    }
    public function is_staff($user_id) {
        $user = get_userdata($user_id);
        return $user && in_array('fourdash_staff', $user->roles);
    }
    public function is_manager($user_id) {
        return in_array('fourdash_manager', $this->get_user_role($user_id));
    }

    public function is_assistant_manager($user_id) {
        return in_array('fourdash_assistant_manager', $this->get_user_role($user_id));
    }

    public function is_employee($user_id) {
        return in_array('fourdash_employee', $this->get_user_role($user_id));
    }
}