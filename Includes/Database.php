<?php
namespace FourDash\Includes;

if (!defined('WPINC')) {
    die;
}

class Database {
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $tables = array(
            $wpdb->prefix . 'fourdash_customers' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_customers (
                id INT AUTO_INCREMENT,
                name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(255),
                PRIMARY KEY (id)
            ) $charset_collate;",
            $wpdb->prefix . 'fourdash_services' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_services (
                id INT AUTO_INCREMENT,
                name VARCHAR(255),
                description TEXT,
                price DECIMAL(10, 2),
                duration INT,
                PRIMARY KEY (id)
            ) $charset_collate;",
            $wpdb->prefix . 'fourdash_appointments' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_appointments (
                id INT AUTO_INCREMENT,
                customer_id INT,
                service_id INT,
                staff_id INT,
                date DATE,
                time TIME,
                status VARCHAR(20),
                PRIMARY KEY (id)
            ) $charset_collate;",
            $wpdb->prefix . 'fourdash_staff' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_staff (
                id INT AUTO_INCREMENT,
                name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(255),
                PRIMARY KEY (id)
            ) $charset_collate;"
        );

        foreach ($tables as $table => $create_query) {
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
                $wpdb->query($create_query);
                $error = $wpdb->last_error;
                if ($error) {
                    return $this->handle_database_error($error);
                }
            }
        }
    }
    public function add_staff($staff_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_staff';
        $result = $wpdb->insert($table_name, $staff_data);
        if ($result === false) {
            return new \WP_Error('db_insert_error', $wpdb->last_error);
        }
        return $wpdb->insert_id;
    }

    public function add_service($service_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_services';
        $result = $wpdb->insert($table_name, $service_data);
        if ($result === false) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return $wpdb->insert_id;
    }
    public function add_customer($customer_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_customers';
        $result = $wpdb->insert($table_name, $customer_data);
        if ($result === false) {
            return new \WP_Error('db_insert_error', $wpdb->last_error);
        }
        return $wpdb->insert_id;
    }

    public function get_public_appointments($start, $end) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';

        $query = $wpdb->prepare(
            "SELECT id, service_id, date, time FROM $table_name 
            WHERE date BETWEEN %s AND %s",
            $start, $end
        );

        $appointments = $wpdb->get_results($query);

        $formatted_appointments = array();
        foreach ($appointments as $appointment) {
            $formatted_appointments[] = array(
                'id' => $appointment->id,
                'title' => 'Booked',
                'start' => $appointment->date . 'T' . $appointment->time,
                'end' => date('Y-m-d H:i:s', strtotime($appointment->date . ' ' . $appointment->time . ' +1 hour')),
            );
        }

        return $formatted_appointments;
    }

    public function remove_tables() {
        global $wpdb;
        $tables = array('fourdash_customers', 'fourdash_services', 'fourdash_appointments', 'fourdash_staff');
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
    }

    public function add_appointment($appointment_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';

        try {
            $result = $wpdb->insert($table_name, $appointment_data);

            if ($result === false) {
                throw new \Exception($wpdb->last_error);
            }

            return $wpdb->insert_id;
        } catch (\Exception $e) {
            error_log('FourDash Database Error: ' . $e->getMessage());
            return new \WP_Error('db_insert_error', $e->getMessage());
        }
    }

    public function edit_appointment($id, $appointment_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $result = $wpdb->update(
            $table_name,
            $appointment_data,
            array('id' => $id)
        );
        if ($result === false) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return $result;
    }

    public function delete_appointment($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $result = $wpdb->delete($table_name, array('id' => $id));
        if ($result === false) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return true;
    }

    public function get_appointments($start, $end) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE date BETWEEN %s AND %s", $start, $end);
        $appointments = $wpdb->get_results($query);
        if ($appointments === null) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return $appointments;
    }
    public function get_service($service_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_services';
        $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $service_id));
        if ($service === null) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return $service;
    }
    public function get_appointments_for_date($date) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $appointments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE date = %s", $date));
        if ($appointments === null) {
            return $this->handle_database_error($wpdb->last_error);
        }
        return $appointments;
    }
    public function get_analytics_data($staff_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $query = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled FROM $table_name";
        if ($staff_id) {
            $query .= $wpdb->prepare(" WHERE staff_id = %d", $staff_id);
        }
        return $wpdb->get_row($query);
    }

    public function get_database_entries($customer_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $entries = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE customer_id = %d", $customer_id));
        if (!$entries) {
            $error = $wpdb->last_error;
            if ($error) {
                return $this->handle_database_error($error);
            } else {
                return array();
            }
        }
        return $entries;
    }

    private function handle_database_error($error) {
        error_log($error);
        return array('error' => $error);
    }
}