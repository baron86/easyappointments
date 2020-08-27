<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bookings_Model extends CI_Model {
    
    public function create($booking) {              
        $bookingStatus='CREATED';
        $booking['status'] = $bookingStatus;        
        $booking['id'] = $this->_insert($booking);        
        $status['status'] = $bookingStatus;
        $status['id_booking'] = (int)$booking['id'];
        $status['description'] = 'Booking created';
        $this->addBookingStatus($status);
        return (int)$booking['id'];
    }
    
    protected function _insert($booking) {
        $this->load->helper('general');        
        $this->db->trans_begin();
        $booking['date'] = date('Y-m-d H:i:s');
        if ( ! $this->db->insert('ea_bookings', $booking)) {
            throw new Exception('Could not insert booking into the database.');
        }
        $bookingId = (int)$this->db->insert_id();
        $this->db->trans_complete();
        return $bookingId;
    }
    
    public function addBookingStatus($status) {       
        $this->load->helper('general');        
        $this->db->trans_begin();
        $status['date'] = date('Y-m-d H:i:s');
        if ( ! $this->db->insert('ea_booking_status', $status)) {
            throw new Exception('Could not insert status into the database.');
        }        
        $this->db->trans_complete();
    }
}
