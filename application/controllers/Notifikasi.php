<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Generated by CRUDigniter v3.2
 * www.crudigniter.com
 */

class Notifikasi extends Admin_Controller{

	function __construct()
	{
		parent::__construct();

		$this->load->model(['Notif_model', 'referensi_model']);
		$this->load->helper('url');
		$this->load->library('pagination');
		if ( ! admin_logged_in()) redirect('login');
	}

	/*
	 * Listing of notifikasi
	 */
	function index()
	{
		$params['limit'] = 20; // jumlah records per halaman
		$params['offset'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

		$filter = $this->input->post('jenis');
		if (isset($filter))
		{
			$this->session->set_userdata('filter', $filter);
		}
		elseif (isset($this->session->filter))
		{
			$filter = $this->session->filter;
		}

		$data['notifikasi'] = $this->Notif_model->get_all_notifikasi($params);

		$data['combo_jenis'] =  $this->Notif_model->get_all_jenis();
		$data['selected_filter'] = $filter;

		$this->load->view('dashboard/header');
		$this->load->view('dashboard/nav');
		$this->load->view('notif/index',$data);
		$this->load->view('dashboard/footer');
	}

	// Ambil view pecahan untuk kolom aksi tabel notifikasi
	private function aksi($data)
	{
		$str = $this->load->view('notif/pajax.index.php', ['data' => $data], TRUE);
    return $str;
	}

  public function ajax_list_notifikasi()
  {
    $list = $this->Notif_model->get_all_notifikasi();

    $data = array();
    $no = $_POST['start'];
    foreach ($list as $notifikasi)
    {
      $no++;
      $row = array();
      $row[] = $no;
      $row[] = $this->aksi($notifikasi);
      $row[] = $notifikasi['frekuensi'];
      $row[] = $notifikasi['kode'];
      $row[] = $notifikasi['judul'];
      $row[] = $notifikasi['jenis'];
      $row[] = $notifikasi['server'];
      $row[] = $notifikasi['isi'];

      $data[] = $row;
    }

    $output = array
    (
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->Notif_model->get_all_notifikasi_count(),
      "recordsFiltered" => count($list),
      "data" => $data,
    );

    //output to json format
    echo json_encode($output);
  }

	/*
	 * Adding a new notifikasi
	 */
	function form($id = null)
	{
		$data['notifikasi'] = null;

		if ($id)
		{
			$data['notifikasi'] = $this->Notif_model->get_notifikasi($id);
			if (empty($data['notifikasi']))
				show_error('Notifikasi itu tidak ditemukan.');
		}

		$this->load->library('form_validation');

		$this->form_validation->set_rules('frekuensi','Frekuensi','required|integer');
		$this->form_validation->set_rules('aktif','Aktif','required');
		$this->form_validation->set_rules('kode','Kode','required|callback_cek_kode');
		$this->form_validation->set_rules('judul','Judul','required');
		$this->form_validation->set_rules('jenis','Jenis','required');
		$this->form_validation->set_rules('server','Server','required');
		$this->form_validation->set_rules('isi','Isi','required');

		if ($this->form_validation->run())
		{
			$params = array(
				'aktif' => $this->input->post('aktif'),
				'frekuensi' => $this->input->post('frekuensi'),
				'kode' => htmlentities($this->input->post('kode')),
				'judul' => htmlentities($this->input->post('judul')),
				'jenis' => $this->input->post('jenis'),
				'server' => $this->input->post('server'),
				'isi' => htmlentities($this->input->post('isi')),
				'aksi_ya' => htmlentities($this->input->post('aksi_ya')),
				'aksi_tidak' => htmlentities($this->input->post('aksi_tidak')),
			);

			if ($id)
				$this->Notif_model->update_notifikasi($id,$params);
			else
				$this->Notif_model->add_notifikasi($params);
			redirect('notifikasi/index');
		}

		$data['status_aktif'] = $this->referensi_model->list_ref(STATUS_AKTIF);
		$data['jenis_notif'] = $this->referensi_model->list_ref(JENIS_NOTIF);
		$data['server_notif'] = $this->referensi_model->list_ref(SERVER_NOTIF);
		$this->load->view('dashboard/header');
		$this->load->view('dashboard/nav');
		$this->load->view('notif/form', $data);
		$this->load->view('dashboard/footer');
	}

	public function cek_kode($kode)
	{
		$id = $this->input->post('id');
		$ada = $this->Notif_model->cek_kode($kode, $id);
		if ($ada)
		{
			$this->form_validation->set_message('cek_kode', 'Kode notifikasi itu sudah ada');
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Deleting notifikasi
	 */
	function remove($id)
	{
		$notifikasi = $this->Notif_model->get_notifikasi($id);

		// check if the notifikasi exists before trying to delete it
		if (isset($notifikasi['id']))
		{
			$this->Notif_model->delete_notifikasi($id);
			redirect('notifikasi/index');
		}
		else
			show_error('The notifikasi you are trying to delete does not exist.');
	}

	public function lock($id = 0, $aktif = 0)
	{
		$this->Notif_model->lock($id, $aktif);
		redirect("notifikasi");
	}

}