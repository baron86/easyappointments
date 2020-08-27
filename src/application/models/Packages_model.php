<?php defined('BASEPATH') OR exit('No direct script access allowed');



class Packages_Model extends CI_Model {
    
    /**
     * This method returns all the services from the database.
     *
     * @return array Returns an object array with all the database services.
     */
    public function get_available_packages()
    {
        $this->db->distinct();
        $packages=$this->db
            ->select('*')
            ->from('ea_packages')
            ->get()->result_array();
        
        foreach( $packages as $key => $package ) {
            $services=$this->db
            ->select('ea_services.*')
            ->from('ea_services,ea_packages_services')
            ->where('ea_services.id = ea_packages_services.id_services')
            ->where('ea_packages_services.id_packages', $package['id'])
            ->get()->result_array();
             $packages[$key]['services'] = $services;
        }
        return $packages;
    }
    
    public function get_batch($where_clause = '')
    {
        $this->db->distinct();
        $packages=$this->db
            ->select('*')
            ->from('ea_packages')
            ->get()->result_array();

        foreach( $packages as $key => $package ) {
            $services=$this->db
            ->select('ea_services.*')
            ->from('ea_services,ea_packages_services')
            ->where('ea_services.id = ea_packages_services.id_services')
            ->where('ea_packages_services.id_packages', $package['id'])
            ->get()->result_array();
             $packages[$key]['services'] = $services;
        }

        // Return package records in an array.
        return $packages;
    }
    
    public function add($package)
    {
        $this->validate($package);

        if ( ! isset($package['id']))
        {
            $package['id'] = $this->_insert($package);
        }
        else
        {
            $package['id'] = $this->_update($package);
        }

        return (int)$package['id'];
    }
    
    public function validate($package)
    {
        $this->load->helper('data_validation');

        // If a package id is present, check whether the record exist in the database.
        if (isset($package['id']))
        {
            $num_rows = $this->db->get_where('ea_packages',
                ['id' => $package['id']])->num_rows();
            if ($num_rows == 0)
            {
                throw new Exception('Provided record id does not exist in the database.');
            }
        }
        
        // Validate required fields.
        if ( ! isset($package['name'])
            || ! isset($package['description'])
            || ! isset($package['units'])
            || ! isset($package['price']))
        {
            throw new Exception('Not all required fields are provided: ' . print_r($package, TRUE));
        }
        
        // Validate package services.
        if ( ! isset($package['services']) || ! is_array($package['services']))
        {
            throw new Exception('Invalid package services given: ' . print_r($package, TRUE));
        }
        else
        { // Check if services are valid int values.
            foreach ($package['services'] as $service_id)
            {
                if ( ! is_numeric($service_id))
                {
                    throw new Exception('A package service with invalid id was found: '
                        . print_r($package, TRUE));
                }
            }
        }

        // Check if package name exists.
        if (isset($package['name']))
        {
            $package_id = (isset($package['id'])) ? $package['id'] : '';
            if ( ! $this->validate_name($package['name'], $package_id))
            {
                throw new Exception ('Package name already exists. Please select a different '
                    . 'name for this record.');
            }
        }
        
        return TRUE;
    }
    
    public function validate_name($name, $package_id)
    {
        $num_rows = $this->db->get_where('ea_packages',
            ['name' => $name, 'id <> ' => $package_id])->num_rows();
        return ($num_rows > 0) ? FALSE : TRUE;
    }
    
     protected function _insert($package)
    {
        $this->load->helper('general');

        // Store package settings and services (must not be present on the $package array).
        $services = $package['services'];
        unset($package['services']);

        // Insert package record and save settings.
        if ( ! $this->db->insert('ea_packages', $package))
        {
            throw new Exception('Could not insert package into the database');
        }

        $package['id'] = $this->db->insert_id();
        $this->save_services($services, $package['id']);

        // Return the new record id.
        return (int)$package['id'];
    }
    
    protected function save_services($services, $package_id)
    {
        // Validate method arguments.
        if ( ! is_array($services))
        {
            throw new Exception('Invalid argument type $services: ' . $services);
        }

        if ( ! is_numeric($package_id))
        {
            throw new Exception('Invalid argument type $package_id: ' . $package_id);
        }

        // Save package services in the database (delete old records and add new).
        $this->db->delete('ea_packages_services', ['id_packages' => $package_id]);
        foreach ($services as $service_id)
        {
            $package_service = [
                'id_packages' => $package_id,
                'id_services' => $service_id
            ];
            $this->db->insert('ea_packages_services', $package_service);
        }
    }
    
    protected function _update($package)
    {
        $this->load->helper('general');

        // Store service and settings (must not be present on the package array).
        $services = $package['services'];
        unset($package['services']);        

        // Update package record.
        $this->db->where('id', $package['id']);
        if ( ! $this->db->update('ea_packages', $package))
        {
            throw new Exception('Could not update package record.');
        }

        $this->save_services($services, $package['id']);

        // Return record id.
        return (int)$package['id'];
    }
    
    public function delete($package_id)
    {
        if ( ! is_numeric($package_id))
        {
            throw new Exception('Invalid argument type $provider_id: ' . $package_id);
        }

        $num_rows = $this->db->get_where('ea_packages', ['id' => $package_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE; // Record does not exist in database.
        }
        $this->db->delete('ea_packages_services', ['id_packages' => $package_id]);
        return $this->db->delete('ea_packages', ['id' => $package_id]);
    }
    
    public function findById($package_id) {
        if ( ! is_numeric($package_id)) {
            throw new Exception('Invalid argument type $package_id: ' . $package_id);
        }
        $package = $this->db->get_where('ea_packages', ['id' => $package_id])->result_array();
        return $package;
    }
}