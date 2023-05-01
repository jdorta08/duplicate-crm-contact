
/*
 * CUSTOM FUNCTION FOR WP FUSION TO CHECK FOR 
 * PHONE NUMBER DUPLICATES IN DYNAMICS 365
 */

function update_contact_id_with_existing_contact( $contact_id, $fields, $user_id, $form_id ) {
	wp_fusion()->crm->connect();
	
   // Get the phone number field value from the submitted form
 	$phone_number = $fields['mobilephone'];
	$user_email_address = $fields['emailaddress1'];
	
	//formatting mobilephone
	$new_phone = substr($phone_number, -1, 2);
	
	$request = wp_fusion()->crm->url . '/contacts?$select=emailaddress1,mobilephone&$top=1&$filter=(endswith(mobilephone,\'' . $new_phone . '\'))';
	$response = wp_safe_remote_get( $request, wp_fusion()->crm->get_params() );
	
	
	if ( is_wp_error( $response ) ) {
			return $response;
		}

	$response = json_decode( wp_remote_retrieve_body( $response ) );
	
	if ( empty( $response ) || empty( $response->value ) ) {
			return false;
		}
	
	// Get the contact ID of the first value in the response
  	$contact_id = $response->value[0]->contactid;
	wp_fusion()->crm->update_contact($contact_id, $fields);

}

add_filter( 'wpf_forms_pre_submission_contact_id', 'update_contact_id_with_existing_contact', 10, 4);
