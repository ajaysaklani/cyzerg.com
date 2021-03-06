<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Data
 * Provides abstraction for submitted data
 *
 * It is backward compatible with version < 2.x
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Data extends IPT_FSQM_Form_Elements_Base {
	public $data;
	public $data_id;
	public $path;
	public $icon_path;
	public $score_img;
	public $email_styling;

	public $admin_update = false;
	public $user_update = false;
	public $send_mail = true;

	public $doing_update;

	public $smtp_conf = array();
	public $reply_to = array();

	public $conditional_hidden_blacklist = array();
	public $conditional_validation_cache = array();

	public function __construct( $data_id = null, $form_id = null ) {
		parent::__construct( $form_id, false );
		// This variable is for droppable design elements which is conditionally hidden
		// We will loop through all design elements and force blacklist those whose parents are hidden conditionally
		$this->conditional_hidden_blacklist = array(
			'layout'   => array(),
			'design'   => array(),
			'mcq'      => array(),
			'freetype' => array(),
			'pinfo'    => array(),
		);
		// This variable is to cache the conditional validation checks so that
		// even if multiple checks occur, it doesn't eat up too much of memory
		$this->conditional_hidden_blacklist = array(
			'layout'   => array(),
			'design'   => array(),
			'mcq'      => array(),
			'freetype' => array(),
			'pinfo'    => array(),
		);
		$this->init( $data_id, $form_id );
		$this->doing_update = false;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global type $ipt_fsqm_info
	 * @param type    $data_id
	 * @param type    $form_id
	 */
	public function init( $data_id = null, $form_id = null ) {
		global $wpdb, $ipt_fsqm_info;
		$this->data = null;
		$this->data_id = null;


		if ( $data_id != null ) {
			//get the raw data
			$this->data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );

			if ( null != $this->data ) {
				//Unserialize it
				$this->data->mcq = maybe_unserialize( $this->data->mcq );
				$this->data->freetype = maybe_unserialize( $this->data->freetype );
				$this->data->pinfo = maybe_unserialize( $this->data->pinfo );

				//Set the new form id
				$form_id = $this->data->form_id;

				//Set the new data id
				$this->data_id = $data_id;
			} else {
				$this->data_id = null;
			}

		}

		//Now call the parent::init if necessary
		if ( $this->form_id != $form_id || $form_id == null ) {
			parent::init( $form_id ); //Parent will set the form_id for us.
		}

		//Call the data standardization
		$this->compat_data();

		// Set the icon path
		$this->path = plugins_url( '/lib/', IPT_FSQM_Loader::$abs_file );
		$theme = $this->get_theme_by_id( $this->settings['theme']['template'] );
		$this->icon_path = $this->path . 'images/icomoon/' . $theme['icons'] . '/PNG/';
		$this->score_img = '<img src="' . $this->icon_path . 'signup.png" height="16" width="16" />';
		$this->email_styling = array(
			'th'              => '',
			'headth'          => '',
			'footth'          => '',
			'td'              => '',
			'tdc'             => 'color: #519548; font-weight: bold;',
			'td_upload'       => '',
			'icons'           => '',
			'iconshead'       => '',
			'th_icon'         => '',
			'td_center'       => '',
			'description'     => '',
			'descriptionhead' => '',
			'table'           => '',
			'inner_table'     => '',
			'tr'              => '',
			'headtr'          => '',
			'foottr'          => '',
			'thead'           => '',
			'tfoot'           => '',
			'tbody'           => '',
			'logo_container'  => '',
			'logo'            => '',
		);
	}

	/*==========================================================================
	 * Form Backend
	 * Can User Edit
	 * Process Save
	 * Email
	 * Score
	 *========================================================================*/
	public function can_user_edit() {
		if ( $this->settings['general']['can_edit'] == false ) {
			return false;
		}

		$limit = (float) $this->settings['general']['edit_time'];
		if ( $limit == 0 ) {
			return true;
		}

		$difference = current_time( 'timestamp' ) - strtotime( $this->data->date );

		if ( $difference > ( $limit * 3600 ) ) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Save the form
	 * @param  bool $admin_update If admin is performing an update Default null = autoguess
	 * @param  bool $user_update  If user is performing an update Default null = autoguess
	 * @param  bool $send_mail    If send emails, default true
	 * @return array              Results
	 */
	public function save_form( $admin_update = null, $user_update = null, $send_mail = null ) {
		$name_prefix = 'ipt_fsqm_form_' . $this->form_id;
		$errors = array();
		//Return if no data
		if ( !isset( $this->post[$name_prefix] ) ) {
			$errors[] = array(
				'id'   => $name_prefix,
				'msgs' => array( __( 'No data submitted.', 'ipt_fsqm' ) ),
			);
			return array(
				'success' => false,
				'errors'  => $errors,
			);
		}

		//Set the data
		$this->data->design   = isset( $this->post[$name_prefix]['design'] )? $this->post[$name_prefix]['design'] : array();
		$this->data->mcq      = isset( $this->post[$name_prefix]['mcq'] )? $this->post[$name_prefix]['mcq'] : array();
		$this->data->freetype = isset( $this->post[$name_prefix]['freetype'] )? $this->post[$name_prefix]['freetype'] : array();
		$this->data->pinfo    = isset( $this->post[$name_prefix ]['pinfo'] )? $this->post[$name_prefix ]['pinfo'] : array();

		//Get the admin remarks if any
		if ( $this->data_id != null && isset( $this->post[$name_prefix]['comment'] ) ) {
			$this->data->comment = $this->post[$name_prefix]['comment'];
		}

		// Set the updation
		if ( $this->data_id == null ) {
			$this->doing_update = false;
			$this->admin_update = false;
			$this->user_update = false;
		} else {
			$this->doing_update = true;
			$this->admin_update = true;
			$this->user_update = false;
		}

		// Override update types if set
		if ( $this->data_id != null && $admin_update !== null && is_bool( $admin_update ) ) {
			$this->admin_update = $admin_update;
		}
		if ( $this->data_id != null && $user_update !== null && is_bool( $user_update ) ) {
			$this->user_update = $user_update;
		}
		if ( $send_mail !== null && is_bool( $send_mail ) ) {
			$this->send_mail = $send_mail;
		}


		//Process it
		return $this->process_save();
	}

	public function process_save() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$errors = array();

		// Loop through layout and design elements and blacklist the hidden ones
		$this->blacklist_conditional_hiddens();

		//Process the pinfo
		foreach ( (array) $this->pinfo as $p_key => $pinfo ) {
			$data = $this->get_submission_from_data( array(
				'type' => $pinfo['type'],
				'm_type' => $pinfo['m_type'],
				'key' => $p_key,
			) );
			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $pinfo, $data, $p_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_pinfo_' . $p_key,
					'msgs' => $error,
				);
			} else {
				$this->data->pinfo[$p_key] = $validation_result['data'];
				//Set the dbmaps
				switch ( $data['type'] ) {
				case 'f_name' :
					$this->data->f_name = $this->data->pinfo[$p_key]['value'];
					break;
				case 'l_name' :
					$this->data->l_name = $this->data->pinfo[$p_key]['value'];
					break;
				case 'email' :
					$this->data->email = $this->data->pinfo[$p_key]['value'];
					break;
				case 'phone' :
					$this->data->phone = $this->data->pinfo[$p_key]['value'];
					break;
				}
			}
		}
		if ( $this->data->f_name == '' ) {
			$this->data->f_name = __( 'Anonymous', 'ipt_fsqm' );
		}

		// Check for email limits, ip limits & user limits
		// But only if this is not an update
		// Now, for an update, we could change the ip address for users
		// But that would hurt the integrity of the submission
		// It is just better to leave the IP address original
		if ( ! $this->doing_update ) {
			if ( $this->settings['limitation']['email_limit'] != 0 && '' != $this->data->email ) {
				$total_emails = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND email = %s", $this->form_id, $this->data->email ) );
				if ( $total_emails >= $this->settings['limitation']['email_limit'] ) {
					$errors[] = array(
						'id' => '',
						'msgs' => array( __( 'Submission limit from this email address has been exceeded.', 'ipt_fsqm' ) )
					);
				}
			}
			if ( $this->settings['limitation']['ip_limit'] != 0 && '' != $this->data->ip ) {
				$total_ip = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND ip = %s", $this->form_id, $this->data->ip ) );
				if ( $total_ip >= $this->settings['limitation']['ip_limit'] ) {
					$errors[] = array(
						'id' => '',
						'msgs' => array( __( 'Submission limit from this IP address has been exceeded.', 'ipt_fsqm' ) )
					);
				}
			}

			if ( $this->settings['limitation']['user_limit'] != 0 && 0 != $this->data->user_id ) {
				$total_users = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d AND user_id = %d", $this->form_id, $this->data->user_id ) );
				if ( $total_users >= $this->settings['limitation']['user_limit'] ) {
					$errors[] = array(
						'id' => '',
						'msgs' => array( __( 'Your submission limit has been exceeded.', 'ipt_fsqm' ) ),
					);
				}
			}
		}

		// Now check if the form requires users to be logged in
		if ( $this->admin_update === false ) {
			if ( $this->settings['limitation']['logged_in'] == true && ! is_user_logged_in() ) {
				$errors[] = array(
					'id' => '',
					'msgs' => array( __( 'You need to be logged in.', 'ipt_fsqm' ) ),
				);
			}
		}

		//Process the mcqs
		$score = 0;
		$max_score = 0;
		foreach ( (array) $this->mcq as $m_key => $mcq ) {
			$data = $this->get_submission_from_data( array(
					'type' => $mcq['type'],
					'm_type' => $mcq['m_type'],
					'key' => $m_key,
				) );
			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $mcq, $data, $m_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_mcq_' . $m_key,
					'msgs' => $error,
				);
			} else {
				$this->data->mcq[$m_key] = $validation_result['data'];

				// Process the score, if there
				// But first check if the element is conditionally hidden
				if ( $validation_result['conditional_hidden'] === true ) {
					continue; // Go to the first of for loop
				}
				$max_possible_score = 0;
				$actual_score = 0;
				$collect_score = false;
				switch ( $mcq['type'] ) {
				case 'radio' :
				case 'select' :
					$possible_scores = array();
					foreach ( $mcq['settings']['options'] as $o_key => $op ) {
						if ( trim( $op['score'] ) != '' ) {
							$collect_score = true;
							$possible_scores[$o_key] = $op['score'];
						} else {
							$possible_scores[$o_key] = 0;
						}
						if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
							$actual_score = $possible_scores[$o_key];
						}
					}
					if ( ! empty( $possible_scores ) ) {
						$max_possible_score = max( $possible_scores );
					}
					break;
				case 'checkbox' :
					foreach ( $mcq['settings']['options'] as $o_key => $op ) {
						if ( '' != trim( $op['score'] ) ) {
							$collect_score = true;
							if ( $op['score'] > 0 ) {
								$max_possible_score += $op['score'];
							}
						}
						if ( in_array( (string) $o_key, $this->data->mcq[$m_key]['options'] ) ) {
							$actual_score += $op['score'];
						}
					}
					break;
				case 'matrix' :
					// Tricky business
					$possible_scores = array();
					$per_row_scores = array();
					foreach ( $mcq['settings']['rows'] as $r_key => $row ) {
						$possible_scores[$r_key] = array();
						$per_row_scores[$r_key] = array();
						foreach ( $mcq['settings']['columns'] as $c_key => $col ) {
							if ( isset( $mcq['settings']['scores'] ) && is_array( $mcq['settings']['scores'] ) && isset( $mcq['settings']['scores'][$c_key] ) && '' != trim( $mcq['settings']['scores'][$c_key] ) ) {
								$collect_score = true;
								$possible_scores[$r_key][] = $mcq['settings']['scores'][$c_key];

								if ( isset( $this->data->mcq[$m_key]['rows'][$r_key] ) && in_array( (string) $c_key, (array) $this->data->mcq[$m_key]['rows'][$r_key] ) ) {
									$per_row_scores[$r_key][] = $mcq['settings']['scores'][$c_key];
								}
							}
						}
					}

					// Now iterate and set the true score
					if ( $collect_score ) {
						foreach ( $per_row_scores as $r_key => $row_scores ) {
							$actual_score += array_sum( (array) $row_scores );
						}

						if ( $mcq['settings']['multiple'] == true ) {
							foreach ( $possible_scores as $r_key => $pscores ) {
								foreach ( (array) $pscores as $mscore ) {
									if ( $mscore > 0 ) {
										$max_possible_score += $mscore;
									}
								}
							}
						} else {
							foreach ( $possible_scores as $r_key => $pscores ) {
								$max_possible_score += max( (array) $pscores );
							}
						}
					}
					break;
				case 'sorting' :
					// Now this is tricky
					$correct = true;
					$correct_positions = array_keys( $mcq['settings']['options'] );
					foreach ( (array) $this->data->mcq[$m_key]['order'] as $o_position => $o_key ) {
						if ( $correct_positions[$o_position] == $o_key ) {
							if ( trim( $mcq['settings']['options'][$o_key]['score'] ) != '' ) {
								$collect_score = true;
							}
							$actual_score += abs( $mcq['settings']['options'][$o_key]['score'] );
						} else {
							$correct = false;
						}
						$max_possible_score += abs( $mcq['settings']['options'][$o_key]['score'] );
					}
					if ( trim( $mcq['settings']['base_score'] ) != '' ) {
						$collect_score = true;
					}
					if ( $mcq['settings']['score_type'] == 'individual' ) {
						if ( $correct == true ) {
							$actual_score += abs( $mcq['settings']['base_score'] );
						}
						$max_possible_score += abs( $mcq['settings']['base_score'] );
					} else {
						$actual_score = $correct ? abs( $mcq['settings']['base_score'] ) : 0;
						$max_possible_score = abs( $mcq['settings']['base_score'] );
					}
					break;
				case 'slider' :
					if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
						$collect_score = true;
						$multiplier = (float) $mcq['settings']['score_multiplier'];
						if ( 0 == $multiplier ) {
							$multiplier = 1;
						}
						$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
						$actual_score = ( (float) $this->data->mcq[$m_key]['value'] * $multiplier );
					}
					break;
				case 'range' :
					if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
						$collect_score = true;
						$multiplier = (float) $mcq['settings']['score_multiplier'];
						if ( 0 == $multiplier ) {
							$multiplier = 1;
						}
						$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
						$actual_score = 0;
						$values = $this->data->mcq[$m_key]['values'];
						switch( $mcq['settings']['formula'] ) {
							case 'avg' :
								$actual_score = ( ( $values['min'] + $values['max'] ) / 2 ) * $multiplier;
								break;
							case 'add' :
								$actual_score = ( ( $values['min'] + $values['max'] ) * $multiplier );
								$max_possible_score = $mcq['settings']['max'] * 2 * $multiplier;
								break;
							case 'diff' :
								$actual_score = ( abs( $values['max'] - $values['min'] ) * $multiplier );
								$max_possible_score = ( abs( $mcq['settings']['max'] - $mcq['settings']['min'] ) * $multiplier );
								break;
							case 'min' :
								$actual_score = ( $values['min'] * $multiplier );
								$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
								break;
							case 'max' :
								$actual_score = ( $values['max'] * $multiplier );
								$max_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
								break;
						}
					}
					break;
				case 'grading' :
					if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
						$collect_score = true;
						$multiplier = (float) $mcq['settings']['score_multiplier'];
						if ( 0 == $multiplier ) {
							$multiplier = 1;
						}
						$max_possible_score = 0;
						$actual_score = 0;
						foreach ( $mcq['settings']['options'] as $sl_key => $sl_option ) {
							if ( ! is_array( $sl_option ) ) {
								// backward compatibility -2.4.0
								$sl_option = array(
									'label' => $sl_option,
									'prefix' => '',
									'suffix' => '',
								);
							}
							foreach ( array( 'min', 'max', 'step' ) as $ovkey ) {
								if ( ! isset( $sl_option[$ovkey] ) || '' == $sl_option[$ovkey] ) {
									$sl_option[$ovkey] = $mcq['settings'][$ovkey];
								}
							}

							$omax_possible_score = max( array( ( $sl_option['min'] * $multiplier ), ( $sl_option['max'] * $multiplier ) ) );
							$oactual_score = 0;
							$values = $this->data->mcq[$m_key]['options'][$sl_key];

							// Range
							if ( $mcq['settings']['range'] == true ) {
								switch( $mcq['settings']['formula'] ) {
									case 'avg' :
										$oactual_score = ( ( $values['min'] + $values['max'] ) / 2 ) * $multiplier;
										break;
									case 'add' :
										$oactual_score = ( ( $values['min'] + $values['max'] ) * $multiplier );
										$omax_possible_score = $mcq['settings']['max'] * 2 * $multiplier;
										break;
									case 'diff' :
										$oactual_score = ( abs( $values['max'] - $values['min'] ) * $multiplier );
										$omax_possible_score = ( abs( $mcq['settings']['max'] - $mcq['settings']['min'] ) * $multiplier );
										break;
									case 'min' :
										$oactual_score = ( $values['min'] * $multiplier );
										$omax_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
										break;
									case 'max' :
										$oactual_score = ( $values['max'] * $multiplier );
										$omax_possible_score = max( array( ( $mcq['settings']['min'] * $multiplier ), ( $mcq['settings']['max'] * $multiplier ) ) );
										break;
								}

							// Slider
							} else {
								$oactual_score = ( (float) $values * $multiplier );
							}

							$max_possible_score += $omax_possible_score;
							$actual_score += $oactual_score;
						}
					}
					break;

				case 'starrating' :
				case 'scalerating' :
					if ( isset( $mcq['settings']['score'] ) && $mcq['settings']['score'] == true ) {
						$collect_score = true;
						$multiplier = (float) $mcq['settings']['score_multiplier'];
						if ( 0 == $multiplier ) {
							$multiplier = 1;
						}

						$max_possible_score = (float) $mcq['settings']['max'] * count( $mcq['settings']['options'] ) * $multiplier;
						$actual_score = 0;
						foreach ( $mcq['settings']['options'] as $r_key => $rating ) {
							$actual_score += (float) $this->data->mcq[$m_key]['options'][$r_key] * $multiplier;
						}
					}

					break;
				}
				if ( $collect_score ) {
					$this->data->mcq[$m_key]['scoredata'] = array(
						'score' => (float) $actual_score,
						'max_score' => (float) $max_possible_score,
					);
					$score += $actual_score;
					$max_score += $max_possible_score;
				}
			}
		}

		// Process the freetype
		$emails = array();
		$freetype_score = 0;
		$freetype_max_score = 0;
		$freetype_upload_cache = array();
		foreach ( (array) $this->freetype as $f_key => $freetype ) {
			$data = $this->get_submission_from_data( array(
				'type' => $freetype['type'],
				'm_type' => $freetype['m_type'],
				'key' => $f_key,
			) );

			//Validate it
			$error = array();
			$validation_result = $this->validate_data_against_element( $freetype, $data, $f_key );
			if ( $validation_result['data_tampering'] == true ) {
				$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
			}
			if ( $validation_result['required_validation'] == false ) {
				$error[] = __( 'Required', 'ipt_fsqm' );
			}
			if ( !empty( $validation_result['errors'] ) ) {
				$error = array_merge( $error, $validation_result['errors'] );
			}
			if ( !empty( $error ) ) {
				$errors[] = array(
					'id' => 'ipt_fsqm_form_' . $this->form_id . '_freetype_' . $f_key,
					'msgs' => $error,
				);
			} else {
				// Save the data to corresponding key
				$this->data->freetype[$f_key] = $validation_result['data'];

				// Now case specific processing
				switch ( $freetype['type'] ) {
					case 'feedback_large' :
					case 'feedback_small' :
						// Save the email
						if ( isset( $freetype['settings']['email'] ) && trim( $freetype['settings']['email'] ) != '' ) {
							$emails_in_freetype = explode( ',', $freetype['settings']['email'] );
							foreach ( $emails_in_freetype as $email ) {
								$email = trim( $email );
								if ( !isset( $emails[$email] ) ) {
									$emails[$email] = array(
										'title' => __( '[WP FSQM Pro] Feedback Notification for ', 'ipt_fsqm' ) . $this->name . ' [' . get_bloginfo( 'name' ) . ']',
										'msgs' => array( '', '<h3>' . $freetype['title'] . '</h3>' . wpautop( $this->data->freetype[$f_key]['value'] ) ),
									);

									if ( $this->settings['admin']['send_from_user'] == true && '' != $this->data->email ) {
										$emails[$email]['from'] = array( $this->data->f_name . ' ' . $this->data->l_name, $this->data->email );
									}
								} else {
									$emails[$email]['msgs'][] = '<h3>' . $freetype['title'] . '</h3>' . wpautop( $this->data->freetype[$f_key]['value'] );
								}
							}
						}

						// Set the score (if any and only if shown)
						if ( $validation_result['conditional_hidden'] === false && $this->admin_update && isset( $this->freetype[$f_key]['settings']['score'] ) && '' != $this->freetype[$f_key]['settings']['score'] && is_numeric( $this->freetype[$f_key]['settings']['score'] ) ) {
							$freetype_max_score += abs( $this->freetype[$f_key]['settings']['score'] );
							if ( '' != $data['score'] && is_numeric( $data['score'] ) ) {
								if ( $data['score'] > $this->freetype[$f_key]['settings']['score'] ) {
									$data['score'] = $this->freetype[$f_key]['settings']['score'];
								}
								$this->data->freetype[$f_key]['score'] = $data['score'];
								$freetype_score += $data['score'];
							}
						}
						break;
					case 'upload' :
						// Cache the keys for later update
						if ( ! isset( $data['id'] ) ) {
							$data['id'] = array();
						}
						$freetype_upload_cache = array_merge( $freetype_upload_cache, (array) $data['id'] );
						break;
				}

			}
		}

		// Process the design, mainly captcha
		if ( $this->data_id == null && !$this->doing_update ) {
			$captchas = $this->get_keys_from_layouts_by_types( 'captcha', $this->layout );
			foreach ( $captchas as $c_key ) {
				$captcha = $this->design[$c_key];
				$data = $this->get_submission_from_data( array(
					'type' => 'captcha',
					'm_type' => 'design',
					'key' => $c_key,
				) );
				//Validate it
				$error = array();
				$validation_result = $this->validate_data_against_element( $captcha, $data, $c_key );
				if ( $validation_result['data_tampering'] == true ) {
					$error[] = __( 'Warning! Data tampering detected.', 'ipt_fsqm' );
				}
				if ( $validation_result['required_validation'] == false ) {
					$error[] = __( 'Required', 'ipt_fsqm' );
				}
				if ( !empty( $validation_result['errors'] ) ) {
					$error = array_merge( $error, $validation_result['errors'] );
				}
				if ( !empty( $error ) ) {
					$errors[] = array(
						'id' => 'ipt_fsqm_form_' . $this->form_id . '_design_' . $c_key,
						'msgs' => $error,
					);
				}
			}
		}


		//Set the scores
		$this->data->score = $score + $freetype_score;
		$this->data->max_score = $max_score + $freetype_max_score;

		//Filter the errors
		$errors = apply_filters( 'ipt_fsqm_filter_data_errors', $errors, $this );

		//Return it
		if ( !empty( $errors ) ) {
			$return = apply_filters( 'ipt_fsqm_filter_save_error', array(
				'success' => false,
				'errors' => $errors,
			) );
			do_action( 'ipt_fsqm_hook_save_error', $this );
			return $return;
		} else {
			// Save it
			if ( $this->data_id == null ) {
				// Insert
				$wpdb->insert( $ipt_fsqm_info['data_table'], array(
					'form_id' => $this->form_id,
					'f_name' => $this->data->f_name,
					'l_name' => $this->data->l_name,
					'email' => $this->data->email,
					'phone' => $this->data->phone,
					'mcq' => maybe_serialize( $this->data->mcq ),
					'freetype' => maybe_serialize( $this->data->freetype ),
					'pinfo' => maybe_serialize( $this->data->pinfo ),
					'ip' => $this->data->ip,
					'star' => $this->data->star,
					'score' => $this->data->score,
					'max_score' => $this->data->max_score,
					'date' => $this->data->date,
					'comment' => $this->data->comment,
					'user_id' => $this->data->user_id,
				), '%s' );
				$this->data_id = $wpdb->insert_id;
				$old_date = strtotime( $wpdb->get_var( $wpdb->prepare( "SELECT updated FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $this->form_id ) ) );
				$new_date = strtotime( $this->data->date );
				if ( $new_date > $old_date ) {
					$wpdb->update( $ipt_fsqm_info['form_table'], array(
						'updated' => $this->data->date,
					), array(
						'id' => $this->form_id,
					), '%s', '%d' );
				}
				do_action( 'ipt_fsqm_hook_save_insert', $this );

				$this->do_integrations();
			} else {
				// Update
				$wpdb->update( $ipt_fsqm_info['data_table'], array(
						'form_id' => $this->form_id,
						'f_name' => $this->data->f_name,
						'l_name' => $this->data->l_name,
						'email' => $this->data->email,
						'phone' => $this->data->phone,
						'mcq' => maybe_serialize( $this->data->mcq ),
						'freetype' => maybe_serialize( $this->data->freetype ),
						'pinfo' => maybe_serialize( $this->data->pinfo ),
						'ip' => $this->data->ip,
						'star' => $this->data->star,
						'score' => $this->data->score,
						'max_score' => $this->data->max_score,
						'comment' => $this->data->comment,
						'user_id' => $this->data->user_id,
					), array(
						'id' => $this->data_id,
					), '%s', '%d' );
				do_action( 'ipt_fsqm_hook_save_update', $this );
			}

			// Update the file upload if any
			if ( ! empty( $freetype_upload_cache ) ) {
				$fileupload_query = $wpdb->prepare( "UPDATE {$ipt_fsqm_info['file_table']} SET data_id = %d WHERE id IN (" . implode( ',', $freetype_upload_cache ) . ")", $this->data_id );
				$wpdb->query( $fileupload_query );
				do_action( 'ipt_fsqm_hook_save_fileupload', $this );
			}

			//Call the API
			do_action( 'ipt_fsqm_hook_save_success', $this );

			// Format string
			$format_string_components = $this->get_format_string();

			// Get the submission quick preview
			ob_start();
			$this->show_quick_preview( true );
			$format_string_components['%SUBMISSION%'] = ob_get_clean();

			// Redirect components
			$redirect_components = array(
				'redirect' => false,
				'redirect_delay' => 0,
				'redirect_url' => '',
				'redirect_top' => $this->settings['redirection']['top'],
			);
			if ( $this->settings['redirection']['type'] != 'none' && $this->admin_update == false ) {
				$redirect_components['redirect'] = true;
				$redirect_components['redirect_delay'] = abs( (int) $this->settings['redirection']['delay'] );
				$redirect_components['redirect_url'] = $this->settings['redirection']['url'];
				if ( $this->settings['redirection']['type'] == 'score' && $this->data->max_score != 0 ) {
					$percentage = $this->data->score * 100 / $this->data->max_score;
					foreach ( $this->settings['redirection']['score'] as $score_range ) {
						if ( $percentage <= $score_range['max'] && $percentage >= $score_range['min'] ) {
							$redirect_components['redirect_url'] = $score_range['url'];
							break;
						}
					}
				}
			}

			if ( trim( $redirect_components['redirect_url'] ) == '%TRACKBACK%' ) {
				$redirect_components['redirect_url'] = $this->get_trackback_url();
			}

			//Tidy the feedback email
			$user_info = sprintf( __( '<p>A new submission has been made. You can visit it at</p><p><strong>%s</strong></p><h4>User Details</h4>', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ) );
			if ( $this->doing_update == true && $this->user_update == true ) {
				$user_info = sprintf( __( '<p>An existing submission has been updated. You can visit it at</p><p><strong>%s</strong></p><h4>User Details</h4>', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' .  $this->data_id ) );
			}
			$user_info .= '<ul>';
			$user_info .= '<li>' . sprintf( __( '<strong>First Name</strong>: %s', 'ipt_fsqm' ), $this->data->f_name ) . '</li>';
			if ( $this->data->l_name != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Last Name</strong>: %s', 'ipt_fsqm' ), $this->data->l_name ) . '</li>';
			}
			if ( $this->data->email != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Email</strong>: <a href="mailto:%1$s">%1$s</a>', 'ipt_fsqm' ), $this->data->email ) . '</li>';
			}
			if ( $this->data->phone != '' ) {
				$user_info .= '<li>' . sprintf( __( '<strong>Phone</strong>: %s', 'ipt_fsqm' ), $this->data->phone ) . '</li>';
			}
			$user_info .= '</ul>';

			foreach ( $emails as $e_key => $email ) {
				$emails[$e_key]['msgs'][0] = $user_info;
				$emails[$e_key]['msgs'] = $this->get_admin_email_template( $emails[$e_key]['msgs'] );
			}

			// Add the user notification
			$user_email = array();
			if ( $this->settings['user']['notification_email'] != '' && $this->data->email != '' ) {
				$user_email[$this->data->email] = array(
					'title' => $this->settings['user']['notification_sub'],
					'from' => array( $this->settings['user']['notification_from'], $this->settings['user']['notification_email'] ),
					'msgs' => $this->user_notification_email(),
					'smtp' => $this->settings['user']['smtp'],
					'smtp_conf' => $this->settings['user']['smtp_config'],
				);
			}

			// Filter it for third party
			$user_email = apply_filters( 'ipt_fsqm_user_email', $user_email, $this );

			// Add the admin notification
			$admin_email = array();
			if ( '' != trim( $this->settings['admin']['email'] ) ) {
				$admin_emails = explode( ',', $this->settings['admin']['email'] );
				foreach ( $admin_emails as $email ) {
					$email = trim( $email );
					$msgs = array();
					$msgs[] = $user_info;
					$msg = ( true == $this->settings['admin']['mail_submission'] ) ? $this->get_admin_email_template( $msgs, true ) : $this->get_admin_email_template( $msgs );
					$admin_email[$email] = array(
						'title' => sprintf( __( '[%s]New Form Submission Notification', 'ipt_fsqm' ), get_bloginfo( 'name' ) ),
						'msgs' => $msg,
					);
					if ( $this->doing_update == true && $this->user_update == true ) {
						$admin_email[$email]['title'] = sprintf( __( '[%s]Form Update Notification', 'ipt_fsqm' ), get_bloginfo( 'name' ) );
					}
					if ( $this->settings['admin']['send_from_user'] == true && '' != $this->data->email ) {
						$admin_email[$email]['from'] = array( $this->data->f_name . ' ' . $this->data->l_name, $this->data->email );
					}
					if ( '' != trim( $ipt_fsqm_settings['email'] ) && $email == $admin_emails[0] ) {
						$admin_email[$email]['cc'] = $ipt_fsqm_settings['email'];
					}
				}
			}

			// Mail it
			if ( $this->doing_update == false || $this->user_update == true ) {
				// New submission or user update email
				if ( $this->send_mail == true ) {
					$this->email( $emails );
					$this->email( $admin_email );
					$this->email( $user_email );
				}
			} elseif ( $this->doing_update == true && $this->admin_update == true && $this->settings['user']['notification_email'] != '' && $this->data->email != '' && isset( $this->post['ipt_fsqm_form_' . $this->form_id]['notify'] ) ) {
				// Admin update email
				$user_email[$this->data->email]['title'] = trim( $this->post['ipt_fsqm_form_' . $this->form_id]['notify_sub'] );
				$this->settings['user']['notification_msg'] = trim( $this->post['ipt_fsqm_form_' . $this->form_id]['notify_msg'] );
				$user_email[$this->data->email]['msgs'] = $this->user_notification_email();
				if ( $this->send_mail == true ) {
					$this->email( $user_email );
				}
				$this->settings['submission']['success_message'] = __( 'If you have a valid user notification email and if you have checked the "Email the surveyee/contributor about this update" button, then the user has been notified with a trackback link.', 'ipt_fsqm' );
			}

			// Finalize the redirect_url
			$redirect_components['redirect_url'] = str_replace( array( '%NAME%', '%FNAME%', '%LNAME%', '%EMAIL%', '%ID%', '%TRACKID%', '%PHONE%', ), array_map( 'urlencode', array(
				$this->data->f_name . ' ' . $this->data->l_name, $this->data->f_name, $this->data->l_name, $this->data->email, $this->data_id, $format_string_components['%TRACK_ID%'], $this->data->phone,
			) ), $redirect_components['redirect_url'] );

			// Add the social sharing buttons if needed
			$social_shares = '';
			if ( $this->settings['social']['show'] == true ) {
				$social_shares = '<p class="ipt_fsqm_social_share">' . $this->social_share_buttons( false ) . '</p>';
			}
			// Return it
			return apply_filters( 'ipt_fsqm_filter_save_success', array(
				'success' => true,
				'components' => $redirect_components,
				'msg' => str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( wptexturize( $this->settings['submission']['success_message'] ) ) ) . $social_shares,
			), $this );
		}
	}

	public function do_integrations() {
		$su_name = '';
		$su_names = array();
		if ( $this->data->f_name != '' ) {
			$su_names[] = $this->data->f_name;
		}
		if ( $this->data->l_name != '' ) {
			$su_names[] = $this->data->l_name;
		}
		if ( ! empty( $su_names ) ) {
			$su_name = implode( ' ', $su_names );
		}

		// MailChimp
		if ( $this->settings['integration']['mailchimp']['enabled'] == true && $this->data->email != '' && '' != $this->settings['integration']['mailchimp']['api'] && '' != $this->settings['integration']['mailchimp']['list_id'] ) {
			require_once IPT_FSQM_Loader::$abs_path . '/integrations/mailchimp/class-ipt-fsqm-intg-mailchimp.php';
			$mc_intg = new IPT_FSQM_INTG_MailChimp( $this->settings['integration']['mailchimp']['api'] );
			$mc_merge_vars = array();
			if ( $this->data->f_name != '' ) {
				$mc_merge_vars['FNAME'] = $this->data->f_name;
			}
			if ( $this->data->l_name != '' ) {
				$mc_merge_vars['LNAME'] = $this->data->l_name;
			}
			try {
				$mc_result = $mc_intg->call( 'lists/subscribe', array(
					'id' => $this->settings['integration']['mailchimp']['list_id'],
					'email' => array( 'email' => $this->data->email ),
					'merge_vars' => $mc_merge_vars,
					'double_optin' => $this->settings['integration']['mailchimp']['double_optin'],
					'update_existing' => true,
					'send_welcome' => $this->settings['integration']['mailchimp']['send_welcome'],
				) );
			} catch(Exception $e ) {

			}
		}

		// Aweber
		if ( $this->settings['integration']['aweber']['enabled'] == true && $this->data->email != '' && '' != $this->settings['integration']['aweber']['authorization_code'] && '' != $this->settings['integration']['aweber']['list_id'] ) {
			if ( ! class_exists( 'AWeberAPI' ) ) {
				require_once IPT_FSQM_Loader::$abs_path . '/integrations/aweber/aweber_api.php';
			}

			try {
				$aw_credentials = AWeberAPI::getDataFromAweberID( $this->settings['integration']['aweber']['authorization_code'] );

				$aw_subscriber = array(
					'email' => $this->data->email,
					'name' => $su_name,
				);

				$aw_application = new AWeberAPI( $aw_credentials[0], $aw_credentials[1] );
				$aw_account = $aw_application->getAccount( $aw_credentials[2], $aw_credentials[3] );
				$aw_list_url = '/accounts/' . $aw_account->id . '/lists/' . $this->settings['integration']['aweber']['list_id'];
				$aw_list = $aw_account->loadFromUrl( $aw_list_url );
				$aw_new_subscriber = $aw_list->subscribers->create( $aw_subscriber );
			} catch ( Exception $e ) {

			}
		}

		// Get Response
		if ( $this->settings['integration']['get_response']['enabled'] == true && $this->data->email != '' && '' != $this->settings['integration']['get_response']['api'] && '' != $this->settings['integration']['get_response']['campaign_id'] ) {
			if ( ! class_exists( 'IPT_FSQM_GetResponse' ) ) {
				require_once IPT_FSQM_Loader::$abs_path . '/integrations/get_response/GetResponseAPI.class.php';
			}

			try {
				$gr_client = new IPT_FSQM_GetResponse( $this->settings['integration']['get_response']['api'] );
				$gr_campaigns = $gr_client->getCampaigns( 'CONTAINS', $this->settings['integration']['get_response']['campaign_id'] );
				$gr_campaigns = (array) $gr_campaigns;
				$gr_campaign_id = array_keys( $gr_campaigns );
				$gr_result = $gr_client->addContact( $gr_campaign_id[0], $su_name, $this->data->email );
			} catch ( Exception $e ) {

			}


		}

		// Campaign Monitor
		if ( $this->settings['integration']['campaign_monitor']['enabled'] == true && $this->data->email != '' && '' != $this->settings['integration']['campaign_monitor']['api'] && '' != $this->settings['integration']['campaign_monitor']['list_id'] ) {
			if ( ! class_exists( 'CS_REST_Subscribers' ) ) {
				require_once IPT_FSQM_Loader::$abs_path . '/integrations/campaign_monitor/csrest_subscribers.php';
			}

			try {
				$cm_auth = array( 'api_key' => $this->settings['integration']['campaign_monitor']['api'] );
				$cm_wrap = new CS_REST_Subscribers( $this->settings['integration']['campaign_monitor']['list_id'], $cm_auth );
				$cm_subscriber = array(
					'EmailAddress' => $this->data->email,
					'Name' => $su_name,
					'Resubscribe' => true,
				);
				$cm_result = $cm_wrap->add( $cm_subscriber );
			} catch ( Exception $e ) {

			}
		}

		do_action( 'ipt_fsqm_hook_integration', $this );
	}

	/**
	 * Get the format string for replacing across following components
	 * User Notification Email
	 * Success messages
	 * Designation
	 * @return array a key => value pair associative, use it like
	 * str_replace( array_keys( $return ), array_values( $return ), subject )
	 */
	public function get_format_string() {
		// Carefully calculate the percentage
		// We do not want to throw any PHP warning for division by zero
		$percentage = null;
		if ( $this->data->max_score != 0 ) {
			$percentage = $this->data->score * 100 / $this->data->max_score;
		}

		// Format string components for finding and replacing contents on
		// User Notification Email
		// Designation
		// Success messages
		$format_string_components = array(
			'%NAME%' => $this->data->f_name . ' ' . $this->data->l_name,
			'%FNAME%' => $this->data->f_name,
			'%LNAME%' => $this->data->l_name,
			'%EMAIL%' => $this->data->email,
			'%PHONE%' => $this->data->phone,
			'%TRACK_LINK%' => $this->get_trackback_url(),
			'%TRACK%' => '<a href="' . esc_attr( $this->get_trackback_url() ) . '">' . __( 'Click Here', 'ipt_fsqm' ) . '</a>',
			'%SCORE%' => $this->data->score . '/' . $this->data->max_score,
			'%OSCORE%' => $this->data->score,
			'%MSCORE%' => $this->data->max_score,
			'%SCOREPERCENT%' => number_format_i18n( (float) $percentage, 2 ) . __( '%', 'ipt_fsqm' ),
			'%DESIGNATION%' => __( 'N/A', 'ipt_fsqm' ),
			'%DESIGNATIONMSG%' => '',
			'%TRACK_ID%' => $this->get_trackback_id(),
			'%PORTAL%' => $this->get_utrackback_url(),
			'%SUBMISSION_ID%' => $this->data_id,
		);

		// Loop through and find the designation
		if ( $this->settings['ranking']['enabled'] == true && $percentage !== null ) {
			foreach ( $this->settings['ranking']['ranks'] as $r_key => $rank ) {
				if ( $percentage <= $rank['max'] && $percentage >= $rank['min'] ) {
					$format_string_components['%DESIGNATION%'] = $rank['title'];
					$format_string_components['%DESIGNATIONMSG%'] = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $rank['msg'] );
					break;
				}
			}
		}

		return $format_string_components;
	}

	public function email( $emails ) {
		if ( !is_array( $emails ) || empty( $emails ) ) {
			return;
		}
		foreach ( $emails as $email => $data ) {
			// Prep the msg
			$msgs = $data['msgs'];
			if ( is_array( $msgs ) ) {
				$msgs = implode( '<br /><br />', $msgs );
			}
			$msgs = '
			<html xmlns="http://www.w3.org/1999/xhtml"><head>
			      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			      <meta name="viewport" content="width=device-width, initial-scale=1.0">
			      <title>' . $data['title'] . '</title>
			      <style type="text/css">
			         /* Client-specific Styles */
			         #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
			         body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background-color: #f6f4f5;}
			         /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
			         .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
			         .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */
			         #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
			         img {outline:none; text-decoration:none;border:none; -ms-interpolation-mode: bicubic;}
			         a img {border:none;}
			         .image_fix {display:block;}
			         p {margin: 0px 0px !important;}

			         table td {border-collapse: collapse;}
			         table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
			         /*a {color: #e95353;text-decoration: none;text-decoration:none!important;}*/
			         /*STYLES*/
			         table[class=full] { width: 100%; clear: both; }

			         /*################################################*/
			         /*IPAD STYLES*/
			         /*################################################*/
			         @media only screen and (max-width: 640px) {
			         a[href^="tel"], a[href^="sms"] {
			         text-decoration: none;
			         color: #ffffff; /* or whatever your want */
			         pointer-events: none;
			         cursor: default;
			         }
			         .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
			         text-decoration: default;
			         color: #ffffff !important;
			         pointer-events: auto;
			         cursor: default;
			         }
			         table[class=devicewidth] {width: 440px!important;text-align:center!important;}
			         table[class=devicewidthinner] {width: 420px!important;text-align:center!important;}
			         table[class="sthide"]{display: none!important;}
			         img[class="bigimage"]{width: 420px!important;height:219px!important;}
			         img[class="col2img"]{width: 420px!important;height:258px!important;}
			         img[class="image-banner"]{width: 440px!important;height:106px!important;}
			         td[class="menu"]{text-align:center !important; padding: 0 0 10px 0 !important;}
			         td[class="logo"]{padding:10px 0 5px 0!important;margin: 0 auto !important;}
			         img[class="logo"]{padding:0!important;margin: 0 auto !important;}

			         }
			         /*##############################################*/
			         /*IPHONE STYLES*/
			         /*##############################################*/
			         @media only screen and (max-width: 480px) {
			         a[href^="tel"], a[href^="sms"] {
			         text-decoration: none;
			         color: #ffffff; /* or whatever your want */
			         pointer-events: none;
			         cursor: default;
			         }
			         .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
			         text-decoration: default;
			         color: #ffffff !important;
			         pointer-events: auto;
			         cursor: default;
			         }
			         table[class=devicewidth] {width: 280px!important;text-align:center!important;}
			         table[class=devicewidthinner] {width: 260px!important;text-align:center!important;}
			         table[class="sthide"]{display: none!important;}
			         img[class="bigimage"]{width: 260px!important;height:136px!important;}
			         img[class="col2img"]{width: 260px!important;height:160px!important;}
			         img[class="image-banner"]{width: 280px!important;height:68px!important;}

			         }
			      </style>


			   </head>
			<body style="background-color: #f6f4f5;">
			' . $msgs . '</body></html>';

			// Prep the header
			$header = array( 'Content-Type: text/html' );
			if ( isset( $data['from'] ) ) {
				$this->reply_to = $data['from'];
				add_action( 'phpmailer_init', array( $this, 'phpmailer_replyto' ) );
			}
			if ( isset( $data['cc'] ) ) {
				$header[] = 'CC: ' . $data['cc'];
			}

			// Prep the attachment
			$attachment = array();
			if ( isset( $data['attachment'] ) ) {
				$attachment = $data['attachment'];
			}

			// Check for SMTP
			// Assumes that a from path is set which will be used as from and from address
			if ( isset( $data['smtp'] ) && $data['smtp'] == true ) {
				$this->smtp_conf = $data['smtp_conf'];

				// We expect the password to be encrypted
				if ( $this->smtp_conf['password'] != '' ) {
					$this->smtp_conf['password'] = $this->decrypt( $this->smtp_conf['password'] );
				}

				// Compat with easy smtp
				if ( function_exists( 'easy_wp_smtp' ) ) {
					remove_action( 'phpmailer_init', 'easy_wp_smtp' );
				}

				// Add our action
				add_action( 'phpmailer_init', array( $this, 'phpmailer_smtp' ) );
			}

			// Mail it
			wp_mail( $email, $data['title'], $msgs, $header, $attachment );

			// Remove our filters
			if ( isset( $data['from'] ) ) {
				$this->reply_to = array();
				remove_action( 'phpmailer_init', array( $this, 'phpmailer_replyto' ) );
			}

			if ( isset( $data['smtp'] ) && $data['smtp'] == true ) {
				// Compat with easy smtp
				if ( function_exists( 'easy_wp_smtp' ) ) {
					add_action( 'phpmailer_init', 'easy_wp_smtp' );
				}

				// Remove our action
				remove_action( 'phpmailer_init', array( $this, 'phpmailer_smtp' ) );
				$this->smtp_conf = array();
			}
		}
	}

	public function phpmailer_replyto( $phpmailer ) {
		if ( empty( $this->reply_to ) || ! is_array( $this->reply_to ) && count( $this->reply_to ) == 2 ) {
			return;
		}
		$phpmailer->From = $this->reply_to[1];
		$phpmailer->FromName = $this->reply_to[0];
		$phpmailer->AddReplyTo( $this->reply_to[1], $this->reply_to[0] );

		// Check if this is non smtp
		if ( empty( $this->smtp_conf ) ) {
			// Add the sender header - WordPress way
			// Get the site domain and get rid of www.
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$sender_email = 'wordpress@' . $sitename;
			$phpmailer->addCustomHeader( 'Sender: <' . $sender_email . '>' );
		}
	}

	public function phpmailer_smtp( $phpmailer ) {
		if ( ! isset( $this->smtp_conf['host'] ) || empty( $this->smtp_conf['host'] ) ) {
			return;
		}
		$phpmailer->Sender = $this->reply_to[1];
		$phpmailer->Mailer = 'smtp';
		$phpmailer->Host = $this->smtp_conf['host'];
		$phpmailer->SMTPSecure = $this->smtp_conf['enc_type'];
		$phpmailer->Port = $this->smtp_conf['port'];
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $this->smtp_conf['username'];
		$phpmailer->Password = $this->smtp_conf['password'];
	}

	/*==========================================================================
	 * Internal APIs - Also Public
	 *========================================================================*/
	public function get_trackback_url() {
		global $ipt_fsqm_settings;
		$query = urlencode( $this->encrypt( $this->data_id ) );
		return add_query_arg( 'id', $query, get_permalink( $ipt_fsqm_settings['track_page'] ) );
	}

	public function get_utrackback_url() {
		global $ipt_fsqm_settings;
		return esc_url( get_permalink( $ipt_fsqm_settings['utrack_page'] ) );
	}

	public function get_trackback_id() {
		return $this->encrypt( $this->data_id );
	}

	public function get_edit_url() {
		return add_query_arg( 'action', 'edit', $this->get_trackback_url() );
	}

	public function compat_data() {
		if ( null == $this->data ) {
			$this->prepare_empty_data();
		} else {
			//check for older format

			if ( empty( $this->data->mcq ) || !is_array( $this->data->mcq ) ) {
				$this->prepare_empty_data_mcq();
			} else {
				$m_keys = array_keys( (array) $this->data->mcq );
				if ( is_string( $this->data->mcq[$m_keys[0]] ) || ( is_array( $this->data->mcq[$m_keys[0]] ) && !isset( $this->data->mcq[$m_keys[0]]['type'] ) ) ) {
					$this->compat_data_mcq();
				}
			}

			if ( empty( $this->data->pinfo ) ) {
				$this->prepare_empty_data_pinfo();
			} else {
				$p_keys = array_keys( (array) $this->data->pinfo );
				if ( is_string( $this->data->pinfo[$p_keys[0]] ) || ( is_array( $this->data->pinfo[$p_keys[0]] ) && !isset( $this->data->pinfo[$p_keys[0]]['type'] ) ) ) {
					$this->compat_data_pinfo();
				}
			}

			if ( empty( $this->data->freetype ) ) {
				$this->prepare_empty_data_freetype();
			} else {
				$f_keys = array_keys( (array) $this->data->freetype );
				if ( is_string( $this->data->freetype[$f_keys[0]] ) || ( is_array( $this->data->freetype[$f_keys[0]] ) && !isset( $this->data->freetype[$f_keys[0]]['type'] ) ) ) {
					$this->compat_data_freetype();
				}
			}
		}
	}

	public function get_submission_from_data( $layout_element ) {
		$return = null;
		if ( isset( $this->data->{$layout_element['m_type']} ) && null !== $this->data->{$layout_element['m_type']} ) {
			if ( isset( $this->data->{$layout_element['m_type']}[$layout_element['key']] ) ) {
				$return = $this->data->{$layout_element['m_type']}[$layout_element['key']];
			}
		}
		return $return;
	}

	public function validate_data_against_element( $element, $data, $key ) {

		//First check for data tamper
		if ( !isset( $data['type'] ) || !isset( $data['m_type'] ) || $data['type'] != $element['type'] || $data['m_type'] != $element['m_type'] ) {
			return array(
				'data_tampering' => true,
				'required_validation' => false,
				'errors' => array( __( 'Type mismatch', 'ipt_fsqm' ) ),
			);
		}

		// Init the variables to get data structure
		$validation_result = array();


		// At this point first check if the item was conditionally hidden
		// If it was hidden, then blank out the data
		// and waive the validations
		// Addresses issue #9
		// @link https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/9
		if ( false == $this->validate_data_against_conditional_logic( $element, $key ) ) {
			$validation_result = array(
				'data_tampering'      => false, // No tampering
				'required_validation' => true, // Passes required validation
				'errors'              => array(), // No errors
				'conditional_hidden'  => true, // It is conditionally hidden
				'data'                => $this->get_submission_structure( $element['type'] ), // Blank out the data
			);
		// The element is shown so proceed as it would
		} else {
			// Merge the POST data with submission structure
			$data = $this->merge_elements( $data, $this->get_submission_structure( $element['type'] ) );
			$param = array( $element, $data, $key );

			// Now pass to the validation
			// Check if callback is defined in element definition
			if ( isset( $this->elements[$element['m_type']][$element['type']]['callback_data_validation'] ) ) {
				$validation_result = call_user_func_array( $this->elements[$element['m_type']][$element['type']]['callback_data_validation'], $param );
			// Not defined, so check if a method exists in this class
			} else {
				if ( method_exists( $this, 'validate_data_against_' . $element['type'] ) ) {
					$validation_result = call_user_func_array( array( $this, 'validate_data_against_' . $element['type'] ), $param );
				} else {
					$validation_result = array(
						'data_tampering'      => false,
						'required_validation' => true,
						'errors'              => array(),
						'conditional_hidden'  => false,
						'data'                => $data,
					);
				}
			}
			$validation_result['conditional_hidden'] = false;
		}

		// But override if a force submission may occur
		if ( $this->settings['timer']['time_limit_type'] != 'none' ) {
			// Reset if invalid data has occured
			if ( $validation_result['required_validation'] != true || !empty( $validation_result['errors'] ) ) {
				$validation_result['required_validation'] = true;
				$validation_result['errors'] = array();
				$validation_result['data'] = $this->get_submission_structure( $element['type'] );
			}
		}

		return $validation_result;
	}

	public function blacklist_conditional_hiddens() {
		// First loop through all layout elements
		foreach ( $this->layout as $l_key => $layout ) {
			$this->check_conditional_for_nested_elements( $layout, $l_key );
		}
		unset( $l_key, $layout );

		// First loop through all design elements and
		// blacklist those other elements whose parents are hidden
		foreach ( $this->design as $d_key => $design ) {
			$design_element_definition = $this->get_element_definition( array(
				'm_type' => $design['m_type'],
				'type' => $design['type'],
			) );
			// No need to test for non-droppable elements
			if ( ! isset( $design_element_definition['droppable'] ) || $design_element_definition['droppable'] == false ) {
				continue;
			}
			// Call the recursive function
			// It will check subsequently for other nested elements
			$this->check_conditional_for_nested_elements( $design, $d_key );
		}
		// Unset for later use
		unset( $d_key, $design );
	}

	public function check_conditional_for_nested_elements( $droppable_element, $key ) {
		// Don't do anything if no other nested element
		if ( ! isset( $droppable_element['elements'] ) || empty( $droppable_element['elements'] ) ) {
			return;
		}

		if ( $droppable_element['m_type'] == 'layout' ) {
			$element = $droppable_element;
		} else {
			$element = $this->get_element_from_layout( array(
				'm_type' => $droppable_element['m_type'],
				'key' => $key,
			) );
		}

		// Now check the conditional state
		$conditional_state = $this->validate_data_against_conditional_logic( $element, $key );

		// Don't do anything if the return state is true (ie, shown)
		if ( $conditional_state == true ) {
			return;
		}

		// First add this to blacklist
		if ( isset( $this->conditional_hidden_blacklist[$droppable_element['m_type']] ) ) {
			if ( ! in_array( $key, $this->conditional_hidden_blacklist[$droppable_element['m_type']]) ) {
				$this->conditional_hidden_blacklist[$droppable_element['m_type']][] = (string) $key;
			}
		}
		// Now loop through all elements inside it and blacklist them as hidden
		foreach ( $droppable_element['elements'] as $elem ) {
			// Recursively blacklist all elements
			$this->blacklist_recursive_elements( $elem );
		}
	}

	public function blacklist_recursive_elements( $elem ) {
		if ( isset( $this->conditional_hidden_blacklist[$elem['m_type']] ) ) {
			if ( ! in_array( $elem['key'], $this->conditional_hidden_blacklist[$elem['m_type']] ) ) {
				$this->conditional_hidden_blacklist[$elem['m_type']][] = (string) $elem['key'];
				// Also reset the data
				$this->data->{$elem['m_type']}[$elem['key']] = $this->get_submission_structure( $elem['type'] );
			}
		}
		$element = $this->get_element_from_layout( $elem );
		if ( isset( $element['elements'] ) && is_array( $element['elements'] ) && ! empty( $element['elements'] ) ) {
			foreach ( $element['elements'] as $nelem ) {
				$this->blacklist_recursive_elements( $nelem );
			}
		}
	}

	/**
	 * Validates an element against it's conditional logic
	 *
	 * The return value doesn't mean whether the element's conditions are satisfied
	 * The approach is made simpler by returing true if the element is supposed to be shown
	 * and returning false is the element is supposed to be hidden
	 *
	 *
	 * @param  array $element
	 * @param  int   $elem_key The key of the element, used for checking inside the blacklist
	 * @return boolean true if the element is shown, false if the element is hidden
	 */
	public function validate_data_against_conditional_logic( $element, $elem_key ) {

		// First check if is already blacklisted
		if ( isset( $this->conditional_hidden_blacklist[$element['m_type']] ) ) {
			if ( in_array( (string) $elem_key, $this->conditional_hidden_blacklist[$element['m_type']] ) ) {
				// It is blacklisted, so return false (ie, hidden because somehow it's parent is also hidden)
				$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = false;
				return false;
			}
		}

		// Check if the valus is cached
		if ( isset( $this->conditional_validation_cache[$element['m_type']] ) ) {
			if ( isset( $this->conditional_validation_cache[$element['m_type']][$elem_key] ) ) {
				if ( $this->conditional_validation_cache[$element['m_type']][$elem_key] == false ) {
					$this->conditional_hidden_blacklist[$element['m_type']][] = $elem_key;
				}
				// If so, return it
				return $this->conditional_validation_cache[$element['m_type']][$elem_key];
			}
		}

		// If no conditional is set, then it is always shown
		if ( ! isset( $element['conditional'] ) || $element['conditional']['active'] == false || empty( $element['conditional']['logic'] ) ) {
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = true;
			return true;
		}


		$return_val = false; // To see if everything checks out
		$relation_check = array();
		$relation_operator = array();

		// Now loop through all logic and check to see if it holds
		foreach ( $element['conditional']['logic'] as $logic_key => $logic ) {
			$cond_elem = $this->get_element_from_layout( array(
				'm_type' => $logic['m_type'],
				'key' => $logic['key'],
			) );

			if ( empty( $cond_elem ) ) {
				continue;
			}

			$cond_data = $this->merge_elements( $this->get_submission_from_data( array(
				'm_type' => $logic['m_type'],
				'key' => $logic['key'],
			) ), $this->get_submission_structure( $cond_elem['type'] ) );

			// What if the logic element, itself is hidden?
			// We wouldn't know unless we do a conditional check on the logic element first
			$this->validate_data_against_conditional_logic( $cond_elem, $logic['key'] );
			// Then simply treat as if the cond_data is emptu
			if ( isset( $this->conditional_hidden_blacklist[$logic['m_type']] ) ) {
				if ( in_array( (string) $logic['key'], $this->conditional_hidden_blacklist[$logic['m_type']] ) ) {
					$cond_data = $this->get_submission_structure( $cond_elem['type'] );
				}
			}


			if ( null == $cond_data || empty( $cond_data ) ) {
				continue;
			}

			// Now switch and check
			$check_against = null;
			switch ( $cond_data['type'] ) {
				case 'radio' :
				case 'p_radio' :
				case 'checkbox' :
				case 'p_checkbox' :
				case 'select' :
				case 'p_select' :
					$check_against = array();
					foreach ( $cond_data['options'] as $o_key ) {
						if ( $o_key === 'others' ) {
							$check_against[] = $cond_elem['settings']['o_label'];
						} else {
							$check_against[] = $cond_elem['settings']['options'][$o_key]['label'];
						}
					}
					break;

				case 'slider' :
					$check_against = $cond_data['value'];
					break;

				case 'range' :
					$check_against = array(
						$cond_data['values']['min'], $cond_data['values']['max'],
					);
					break;

				case 'spinners' :
				case 'starrating' :
				case 'scalerating' :
					$check_against = array();
					foreach( $cond_data['options'] as $oval ) {
						if ( $oval != '' ) {
							$check_against[] = $oval;
						}
					}
					break;

				case 'grading' :
					$check_against = array();
					foreach ( $cond_data['options'] as $oval ) {
						if ( is_array( $oval ) ) {
							if ( $oval['min'] != '' ) {
								$check_against[] = $oval['min'];
							}
							if ( $oval['max'] != '' ) {
								$check_against[] = $oval['max'];
							}
						} else {
							if ( $oval != '' ) {
								$check_against[] = $oval;
							}
						}
					}
					break;

				case 'matrix' :
					$check_against = array();
					foreach ( $cond_data['rows'] as $cols ) {
						foreach ( $cols as $c_key ) {
							$label = $cond_elem['settings']['columns'][$c_key];
							if ( ! in_array( $label, $check_against ) ) {
								$check_against[] = $label;
							}
						}
					}
					break;

				case 'toggle' :
				case 's_checkbox' :
					$check_against = ( ! isset( $cond_data['value'] ) || empty( $cond_data['value'] ) || $cond_data['value'] == null ) ? '0' : '1';
					break;

				case 'feedback_small' :
				case 'f_name' :
				case 'l_name' :
				case 'email' :
				case 'phone' :
				case 'p_name' :
				case 'p_email' :
				case 'p_phone' :
				case 'textinput' :
				case 'password' :
				case 'keypad' :
				case 'feedback_large' :
				case 'textarea' :
					$check_against = $cond_data['value'];
					break;

				case 'upload' :
					$check_against = count( $cond_data['id'] );
					break;

				case 'mathematical' :
					$check_against = (float) $cond_data['value'];
					break;

				case 'address' :
					$check_against = array(
						$cond_data['recipient'],
						$cond_data['line_one'],
						$cond_data['line_two'],
						$cond_data['line_three'],
						$cond_data['country'],
					);
					break;

				case 'datetime' :
					$check_against = strtotime( $cond_data['value'] );
					$logic['value'] = strtotime( $logic['value'] );
					break;
				default :

					break;
			}

			$this_validated = false;
			$final_compare_against = null;
			$final_compare_with = strip_shortcodes( $logic['value'] );

			// Now strip shortcodes from possible labels
			if ( is_array( $check_against ) ) {
				$check_against = array_map( 'strip_shortcodes', $check_against );
				$check_against = array_map( 'strip_tags', $check_against );
			} else {
				$check_against = strip_tags( strip_shortcodes( $check_against ) );
			}

			if ( $logic['check'] === 'val' ) {
				if ( is_array( $check_against ) ) {
					$final_compare_against = array();
					foreach ( $check_against as $ca ) {
						$final_compare_against[] = trim( strtolower( $ca ) );
					}
				} else {
					$final_compare_against = trim( strtolower( $check_against ) );
				}
				$final_compare_with = trim( strtolower( $final_compare_with ) );
			} else {
				$final_compare_against = is_array( $check_against ) ? count( $check_against ) : (float) strlen( $check_against );
				$final_compare_with = (float) $final_compare_with;
			}

			$compare_against_array = is_array( $final_compare_against );

			switch ( $logic['operator'] ) {
				case 'eq':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && $value == $final_compare_with ) {
								$this_validated = true;
								break;
							} elseif ( $value === '' && $final_compare_with === '' ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against !== '' && $final_compare_against == $final_compare_with ) {
							$this_validated = true;
						} elseif ( $final_compare_against === '' && $final_compare_with === '' ) {
							$this_validated = true;
							break;
						}
					}
					break;

				case 'neq':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && $value != $final_compare_with ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against !== '' && $final_compare_against != $final_compare_with ) {
							$this_validated = true;
						}
					}
					break;

				case 'gt':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value > $final_compare_with ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against > $final_compare_with ) {
							$this_validated = true;
						}
					}
					break;

				case 'lt':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value < $final_compare_with ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against < $final_compare_with ) {
							$this_validated = true;
						}
					}
					break;

				case 'ct':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && strstr( $value, $final_compare_with ) !== FALSE ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against !== '' && strstr( $final_compare_against, $final_compare_with ) !== FALSE ) {
							$this_validated = true;
						}
					}
					break;

				case 'dct':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( $value !== '' && strstr( $value, $final_compare_with ) === FALSE ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( $final_compare_against !== '' && strstr( $final_compare_against, $final_compare_with ) === FALSE ) {
							$this_validated = true;
						}
					}
					break;

				case 'sw':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( preg_match( '/^' . $final_compare_with . '/im', $value ) ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( preg_match( '/^' . $final_compare_with . '/im', $final_compare_against ) ) {
							$this_validated = true;
						}
					}
					break;

				case 'ew':
					if ( $compare_against_array ) {
						foreach ( $final_compare_against as $value ) {
							if ( preg_match( '/' . $final_compare_with . '$/im', $value ) ) {
								$this_validated = true;
								break;
							}
						}
					} else {
						if ( preg_match( '/' . $final_compare_with . '$/im', $final_compare_against ) ) {
							$this_validated = true;
						}
					}
					break;

				default:
					$this_validated = false;
					break;
			}

			$relation_check[$logic_key] = $this_validated;
			$relation_operator[$logic_key] = $logic['rel'];
		}

		// Now check individual if necessary
		$relation_check_against = null;
		$relation_check_operator = null;
		$relation_check_array = array();
		$relation_check_array_key = 0;
		foreach ( $relation_check as $logic_key => $val ) {
			if ( null === $relation_check_against ) {
				$relation_check_against = $val;
			} else {
				switch ( $relation_check_operator ) {
					case 'and':
						$relation_check_against = $relation_check_against && $val;
						break;

					case 'or' :
						$relation_check_array_key++;
						$relation_check_against = $val;
					default:
						# code...
						break;
				}
			}
			$relation_check_operator = $relation_operator[$logic_key];
			$relation_check_array[$relation_check_array_key] = $relation_check_against;
		}

		$return_val = null;
		foreach ( $relation_check_array as $group_result ) {
			if ( $return_val === null ) {
				$return_val = $group_result;
			} else {
				$return_val = $return_val || $group_result;
			}
		}

		if ( $return_val ) { // All conditions checks out
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = $element['conditional']['change'];
		} else { // Initial status
			$this->conditional_validation_cache[$element['m_type']]["$elem_key"] = $element['conditional']['status'];
		}

		// Blacklist
		if ( $this->conditional_validation_cache[$element['m_type']][$elem_key] == false ) {
			$this->conditional_hidden_blacklist[$element['m_type']][] = $elem_key;
		}

		return $this->conditional_validation_cache[$element['m_type']]["$elem_key"];
	}

	public function validate_data_against_captcha( $element, $data ) {
		$data_tampering = false;
		$required_validation = true;
		$errors = array();

		if ( trim( $data['hash'] ) == '' ) {
			$data_tampering = true;
		} else if ( $data['value'] == '' ) {
				$required_validation = false;
			} else {
			$value = $this->decrypt( $data['hash'] );
			if ( $value != $data['value'] ) {
				$errors[] = __( 'Security Captcha is invalid.', 'ipt_fsqm' );
			}
		}


		return array(
			'data_tampering' => $data_tampering,
			'required_validation' => $required_validation,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_radio( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_select( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_slider( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'slider', 'value', $element );
	}

	public function validate_data_against_range( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'range', 'values', $element );
	}

	public function validate_data_against_spinners( $element, $data ) {
		$element['validation']['filters']['type'] = 'number';
		return $this->validate_and_sanitize_data_against_element( $data, 'grading', 'options', $element );
	}

	public function validate_data_against_grading( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'grading', 'options', $element );
	}

	public function validate_data_against_starrating( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'ratings', 'options', $element );
	}

	public function validate_data_against_scalerating( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'ratings', 'options', $element );
	}

	public function validate_data_against_matrix( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'matrix', 'rows', $element );
	}

	public function validate_data_against_toggle( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'toggle', 'value', $element );
	}

	public function validate_data_against_sorting( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'sorting', 'order', $element );
	}

	public function validate_data_against_feedback_large( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_feedback_small( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_f_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'personName',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_l_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'personName',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_upload( $element, $data, $key ) {
		$required_validation = true;
		$data_tampering = false;
		$errors = array();

		if ( $element['type'] !== $data['type'] || $element['m_type'] !== $data['m_type'] ) {
			$data_tampering = true;
		}

		if ( $element['settings']['max_number_of_files'] >= 1 && $element['validation']['required'] === true && empty( $data['id'] ) ) {
			$required_validation = false;
		}

		if ( $element['settings']['min_number_of_files'] > 1 && count( $data['id'] ) < $element['settings']['min_number_of_files'] ) {
			$errors[] = sprintf( __( 'At least %d files required.', 'ipt_fsqm' ), $element['settings']['min_number_of_files'] );
		}
		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	public function validate_data_against_email( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'email',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_phone( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'phone',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_name( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'personName',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_email( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'email',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_phone( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		$element['validation']['filters'] = array(
			'type' => 'phone',
		);
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_textinput( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_textarea( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_password( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_radio( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_p_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_p_select( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'options', 'options', $element );
	}

	public function validate_data_against_s_checkbox( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 's_checkbox', 'value', $element );
	}

	public function validate_data_against_address( $element, $data, $key ) {
		foreach ( $data['values'] as $v_key => $val ) {
			$data[$v_key] = strip_tags( $val );
		}
		return $this->validate_and_sanitize_data_against_element( $data, 'address', 'values', $element );
	}

	public function validate_data_against_keypad( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_datetime( $element, $data, $key ) {
		$data['value'] = strip_tags( $data['value'] );
		return $this->validate_and_sanitize_data_against_element( $data, 'value', 'value', $element );
	}

	public function validate_data_against_p_sorting( $element, $data ) {
		return $this->validate_and_sanitize_data_against_element( $data, 'sorting', 'order', $element );
	}

	/**
	 * Validates all predefined elements and sanitizes data
	 *
	 * @param array   $data        The original user submitted data array
	 * @param string  $check_type  Type of checking
	 * @param string  $check_where The key to the $data array where the submission result is shown
	 * @param array   $element     The element to which the data would be validated
	 * @return An associative array with the following components
	 *                        		'required_validation' => $required_validation, //If true then it passes the required validation test
	 *                          	'data_tampering' => $data_tampering, //If true then the data has been tampered
	 *                        		'errors' => $errors, //If not empty then other errors were found
	 */
	public function validate_and_sanitize_data_against_element( $data, $check_type, $check_where, $element ) {
		$errors = array();
		$required_validation = true;
		$data_tampering = false;
		if ( !isset( $data[$check_where] ) || empty( $data[$check_where] ) ) {
			$required_validation = false;
		}


		$user_submitted = isset( $data[$check_where] ) ? $data[$check_where] : false;

		switch ( $check_type ) {
		case 'string' :
			if ( !is_string( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				if ( '' == $user_submitted ) {
					$required_validation = false;
				} else {
					$required_validation = true;
				}
			}
			break;
		case 'options' :
			// If the submission is empty then cast it to array
			// Issue #10
			if ( empty( $user_submitted ) ) {
				$user_submitted = array();
			}
			if ( !is_array( $user_submitted ) ) {
				$data_tampering = true;
				$errors[] = __( 'Submitted Data is not an array.', 'ipt_fsqm' );
			} else {
				foreach ( $user_submitted as $key => $val ) {
					if ( preg_match( '/[0-9]+/', $val ) ) {
						if ( isset( $element['settings']['options'][(int) $val] ) ) {
							continue;
						} else {
							$data_tampering = true;
							$errors[] = __( 'Invalid Option.', 'ipt_fsqm' );
						}
					} else if ( $val == 'others' ) {
							if ( '' == $data['others'] ) {
								$errors[] = __( 'No opinion provided.', 'ipt_fsqm' );
							}
					} else if ( $val == '' ) {
						$required_validation = false;
					} else {
						$data_tampering = true;
						$errors[] = __( 'Invalid Submission Value', 'ipt_fsqm' );
					}
				}
				if ( !$data_tampering && empty( $errors ) && !empty( $user_submitted ) ) {
					$required_validation = true;
				}
			}
			break;
		case 'value' :
			if ( !is_string( $user_submitted ) ) {
				$data_tampering = true;
			} else if ( '' == $user_submitted ) {
					$required_validation = false;
				} else {
				$required_validation = true;
			}
			break;
		case 'slider' :
			if ( !is_string( $user_submitted ) && !is_int( $user_submitted ) && !is_float( $user_submitted ) ) {
				$data_tampering = true;
			} else if ( $user_submitted == '' || ( '' != $element['settings']['min'] && (float) $user_submitted < $element['settings']['min'] ) || ( '' != $element['settings']['max'] && (float) $user_submitted > $element['settings']['max'] ) ) {
					$data_tampering = true;
					$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s', 'ipt_fsqm' ), $element['settings']['min'], $element['settings']['max'], $user_submitted );
				} else {
				$required_validation = true;
			}
			break;
		case 'range' :
			if ( !is_array( $user_submitted ) || !isset( $user_submitted['min'] ) || !isset( $user_submitted['max'] ) ) {
				$data_tampering = true;
			} else if ( ( '' != $element['settings']['min'] && $user_submitted['min'] < $element['settings']['min'] ) || ( '' != $element['settings']['max'] && $user_submitted['max'] > $element['settings']['max'] ) ) {
					$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s, %s', 'ipt_fsqm' ), $element['settings']['min'], $element['settings']['max'], isset( $user_submitted['min'] ) ? $user_submitted['min'] : '', isset( $user_submitted['max'] ) ? $user_submitted['max'] : '' );
					$data_tampering = true;
				} else if ( $user_submitted['max'] == '' || $user_submitted['min'] == '' ) {
					$required_validation = false;
				} else {
				$required_validation = true;
			}
			break;
		case 'grading' :
			// If the submission is empty then cast it to array
			// Issue #10
			if ( empty( $user_submitted ) ) {
				$user_submitted = array();
			}
			if ( !is_array( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				//Can either be array or string
				foreach ( $user_submitted as $key => $val ) {
					// Compat with v-2.5.0
					if ( ! is_array( $element['settings']['options']["$key"] ) ) {
						$element['settings']['options']["$key"] = array(
							'label' => $element['settings']['options']["$key"],
						);
					}
					foreach ( array( 'min', 'max', 'step' ) as $validator ) {
						if ( ! isset( $element['settings']['options']["$key"][$validator] ) || $element['settings']['options']["$key"][$validator] == '' ) {
							$element['settings']['options']["$key"][$validator] = $element['settings'][$validator];
						}
					}
					if ( is_string( $val ) || is_float( $val ) || is_int( $val ) ) {
						if ( ( '' != $element['settings']['options']["$key"]['min'] && $val < $element['settings']['options']["$key"]['min'] ) || ( '' != $element['settings']['options']["$key"]['max'] && $val > $element['settings']['options']["$key"]['max'] ) ) {
							if ( $element['type'] != 'spinners' ) {
								$data_tampering = true;
							}
							if ( $element['type'] != 'spinners' || '' != $val )
								$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s', 'ipt_fsqm' ), $element['settings']['options']["$key"]['min'], $element['settings']['options']["$key"]['max'], $val );
						} else if ( '' == $val ) {
								$required_validation = false;
							} else {
							$required_validation = true;
						}
					} else if ( is_array( $val ) ) {
							if ( !isset( $val['min'] ) || !isset( $val['max'] ) || ( '' != $element['settings']['options']["$key"]['min'] && $val['min'] < $element['settings']['options']["$key"]['min'] ) || ( '' != $element['settings']['options']["$key"]['max'] && $val['max'] > $element['settings']['options']["$key"]['max'] ) ) {
								$data_tampering = true;
								$errors[] = sprintf( __( 'Out of range. Minimum allowed: %s, Maximum allowed: %s, Given: %s, %s', 'ipt_fsqm' ), $element['settings']['options']["$key"]['min'], $element['settings']['options']["$key"]['max'], isset( $val['min'] ) ? $val['min'] : '', isset( $val['max'] ) ? $val['max'] : '' );
							} else {
								$required_validation = true;
							}
						} else {
						$data_tampering = true;
					}
				}
			}
			break;
		case 'ratings' :
			// If the submission is empty then cast it to array
			// Issue #10
			if ( empty( $user_submitted ) ) {
				$user_submitted = array();
			}
			if ( !is_array( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				//Can either be array or string
				foreach ( $user_submitted as $key => $val ) {
					if ( is_string( $val ) || is_float( $val ) || is_int( $val ) ) {
						if ( ( '' != $element['settings']['max'] && $val > $element['settings']['max'] ) ) {
							$data_tampering = true;
							$errors[] = sprintf( __( 'Out of range. Maximum allowed: %1$s, Given: %2$s', 'ipt_fsqm' ), $element['settings']['max'], $val );
						} else if ( '' == $val ) {
								$required_validation = false;
							} else {
							$required_validation = true;
						}
					} else {
						$data_tampering = true;
					}
				}
			}
			break;
		case 'matrix' :
			// If the submission is empty then cast it to array
			// Issue #10
			if ( empty( $user_submitted ) ) {
				$user_submitted = array();
			}
			if ( !is_array( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				foreach ( $user_submitted as $key => $val ) {
					if ( !isset( $element['settings']['rows'][$key] ) ) {
						$data_tampering = true;
					} else if ( !is_array( $val ) || empty( $val ) ) {
							$required_validation = false;
						} else {
						foreach ( $val as $col ) {
							if ( !isset( $element['settings']['columns'][$col] ) ) {
								$data_tampering = true;
							}
						}
						if ( !$data_tampering ) {
							$required_validation = true;
						}
					}
				}
			}
			break;
		case 'sorting' :
			if ( !is_array( $user_submitted ) || count( $user_submitted ) != count( $element['settings']['options'] ) ) {
				$data_tampering = true;
			} else {
				foreach ( $user_submitted as $o_key ) {
					if ( !isset( $element['settings']['options'][$o_key] ) ) {
						$data_tampering = true;
						break;
					}
				}
				if ( !$data_tampering ) {
					$required_validation = true;
				} else {
					$required_validation = false;
				}
			}
			break;
		case 'toggle' :
			if ( !is_string( $user_submitted ) && !is_bool( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				$required_validation = true;
			}
			break;
		case 's_checkbox' :
			if ( !is_string( $user_submitted ) && !is_bool( $user_submitted ) ) {
				$data_tampering = true;
			} else {
				if ( false == $user_submitted ) {
					$required_validation = false;
				}
			}
			break;
		case 'address' :
			if ( !is_array( $user_submitted ) || !isset( $user_submitted['recipient'] ) || !isset( $user_submitted['line_one'] ) || !isset( $user_submitted['line_two'] ) || !isset( $user_submitted['line_three'] ) || !isset( $user_submitted['country'] ) ) {
				$data_tampering = true;
			} else {
				if ( '' == $user_submitted['recipient'] || '' == $user_submitted['line_one'] || '' == $user_submitted['line_two'] || '' == $user_submitted['country'] ) {
					$required_validation = false;
				} else {
					$required_validation = true;
				}
			}
		}

		/**
		 * ReqValidation    ElemValidation      Result
		 *      0                   0               1
		 *      0                   1               0
		 *      1                   0               1
		 *      1                   1               1
		 */
		if ( isset( $element['validation'] ) && isset( $element['validation']['required'] ) ) {
			if ( $required_validation == false && $element['validation']['required'] == true ) {
				$required_validation = false;
			} else {
				$required_validation = true;
			}
		} else {
			//It will only fallback to ranges, sliders etc, where it is required to validate
		}

		if ( !$data_tampering && isset( $element['validation'] ) && isset( $element['validation']['filters'] ) ) {
			$errors = array_merge( $errors, $this->validate_reg_exp( $user_submitted, $element['validation']['filters'], $element['validation']['required'], $element ) );
		}

		return array(
			'required_validation' => $required_validation,
			'data_tampering' => $data_tampering,
			'errors' => $errors,
			'data' => $data,
		);
	}

	protected function validate_reg_exp( $value, $validation_filters, $required = false, $element = array() ) {
		$reg_exp = $this->get_reg_exp();
		$errors = array();
		$to_check = $required || !empty( $value );

		//Types
		if ( isset( $validation_filters['type'] ) && isset( $reg_exp[$validation_filters['type']] ) && $to_check ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $val ) {
					if ( !preg_match( $reg_exp[$validation_filters['type']]['regex'], $val ) ) {
						$errors[] = $reg_exp[$validation_filters['type']]['alertText'];
					}
				}
			} else {
				if ( !preg_match( $reg_exp[$validation_filters['type']]['regex'], (string) $value ) ) {
					$errors[] = $reg_exp[$validation_filters['type']]['alertText'];
				}
			}

		}


		$min_max_check = array( 'number', 'integer' );
		$minsize_maxsize_check = array( 'all', 'onlyNumberSp', 'onlyLetterSp', 'onlyLetterNumber', 'onlyLetterNumberSp', 'noSpecialCharacter' );

		//Min & Max (Float)

		if ( isset( $validation_filters['type'] ) && in_array( $validation_filters['type'], $min_max_check ) ) {
			if ( isset( $validation_filters['min'] ) && $validation_filters['min'] != '' && $to_check ) {
				if ( (float) $value < $validation_filters['min'] ) {
					$errors[] = $reg_exp['min']['alertText'] . $validation_filters['min'];
				}
			}
			if ( isset( $validation_filters['max'] ) && $validation_filters['max'] != '' && $to_check ) {
				if ( (float) $value > $validation_filters['max'] ) {
					$errors[] = $reg_exp['max']['alertText'] . $validation_filters['max'];
				}
			}
		}


		//minSize & maxSize
		if ( isset( $validation_filters['type'] ) && in_array( $validation_filters['type'], $minsize_maxsize_check ) ) {
			if ( isset( $validation_filters['minSize'] ) && $validation_filters['minSize'] != '' && $to_check ) {
				if ( strlen( (string) $value ) < $validation_filters['minSize'] ) {
					$errors[] = $reg_exp['minSize']['alertText'] . $validation_filters['minSize'] . $reg_exp['minSize']['alertText2'];
				}
			}
			if ( isset( $validation_filters['maxSize'] ) && $validation_filters['maxSize'] != '' && $to_check ) {
				if ( strlen( (string) $value ) > $validation_filters['maxSize'] ) {
					$errors[] = $reg_exp['maxSize']['alertText'] . $validation_filters['maxSize'] . $reg_exp['maxSize']['alertText2'];
				}
			}
		}


		//past & future
		if ( !empty( $element ) && $element['type'] == 'datetime' && isset( $element['settings'] ) && isset( $element['settings']['type'] ) && $element['settings']['type'] != 'time' ) {
			if ( isset( $validation_filters['past'] ) && $validation_filters['past'] != '' && $to_check ) {
				$past = strtolower( $validation_filters['past'] ) == 'now' ? current_time( 'timestamp' ) : strtotime( $validation_filters['past'] );
				if ( strtotime( $value ) > $past ) {
					$errors[] = $reg_exp['past']['alertText'] . $validation_filters['past'];
				}
			}
			if ( isset( $validation_filters['future'] ) && $validation_filters['future'] != '' && $to_check ) {
				$future = strtolower( $validation_filters['future'] ) == 'now' ? current_time( 'timestamp' ) : strtotime( $validation_filters['future'] );
				if ( strtotime( $value ) < $future ) {
					$errors[] = $reg_exp['future']['alertText'] . $validation_filters['future'];
				}
			}
		}

		//minCheckbox & maxCheckbox
		if ( isset( $validation_filters['minCheckbox'] ) && $validation_filters['minCheckbox'] != '' ) {
			if ( count( (array) $value ) < (float) $validation_filters['minCheckbox'] ) {
				$errors[] = $reg_exp['minCheckbox']['alertText'] . $validation_filters['minCheckbox'] . $reg_exp['minCheckbox']['alertText2'];
			}
		}
		if ( isset( $validation_filters['maxCheckbox'] ) && $validation_filters['maxCheckbox'] != '' ) {
			if ( count( (array) $value ) > (float) $validation_filters['maxCheckbox'] ) {
				$errors[] = $reg_exp['maxCheckbox']['alertText'] . $validation_filters['maxCheckbox'] . $reg_exp['maxCheckbox']['alertText2'];
			}
		}

		return $errors;
	}

	protected function get_reg_exp() {
		$reg_exp = array(
			'required' => array(
				'alertText' =>  __( '* This field is required', 'ipt_fsqm' ),
				'alertTextCheckboxMultiple' =>  __( '* Please select an option', 'ipt_fsqm' ),
				'alertTextCheckboxe' =>  __( '* This checkbox is required', 'ipt_fsqm' ),
				'alertTextDateRange' =>  __( '* Both date range fields are required', 'ipt_fsqm' )
			),
			'requiredInFunction' => array(
				'alertText' =>  __( '* Field must equal test', 'ipt_fsqm' )
			),
			'dateRange' => array(
				'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Date Range', 'ipt_fsqm' )
			),
			'dateTimeRange' => array(
				'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Date Time Range', 'ipt_fsqm' )
			),
			'minSize' => array(
				'alertText' =>  __( '* Minimum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' characters required', 'ipt_fsqm' )
			),
			'maxSize' => array(
				'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' characters allowed', 'ipt_fsqm' )
			),
			'groupRequired' => array(
				'alertText' =>  __( '* You must fill one of the following fields', 'ipt_fsqm' )
			),
			'min' => array(
				'alertText' =>  __( '* Minimum value is ', 'ipt_fsqm' )
			),
			'max' => array(
				'alertText' =>  __( '* Maximum value is ', 'ipt_fsqm' )
			),
			'past' => array(
				'alertText' =>  __( '* Date prior to ', 'ipt_fsqm' )
			),
			'future' => array(
				'alertText' =>  __( '* Date past ', 'ipt_fsqm' )
			),
			'maxCheckbox' => array(
				'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' option(s) allowed', 'ipt_fsqm' )
			),
			'minCheckbox' => array(
				'alertText' =>  __( '* Please select ', 'ipt_fsqm' ),
				'alertText2' =>  __( ' option(s)', 'ipt_fsqm' )
			),
			'equals' => array(
				'alertText' =>  __( '* Fields do not match', 'ipt_fsqm' )
			),
			'creditCard' => array(
				'alertText' =>  __( '* Invalid credit card number', 'ipt_fsqm' )
			),
			'phone' => array(
				// credit => jquery.h5validate.js / orefalo
				'regex' => "/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/",
				'alertText' =>  __( '* Invalid phone number', 'ipt_fsqm' )
			),
			'email' => array(
				// HTML5 compatible email regex ( http =>//www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
				'regex' => "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",
				'alertText' =>  __( '* Invalid email address', 'ipt_fsqm' )
			),
			'integer' => array(
				'regex' => "/^[\-\+]?\d+$/",
				'alertText' =>  __( '* Not a valid integer', 'ipt_fsqm' )
			),
			'number' => array(
				// Number, including positive, negative, and floating decimal. credit => orefalo
				'regex' => "/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/",
				'alertText' =>  __( '* Invalid floating decimal number', 'ipt_fsqm' )
			),
			'date' => array(
				// Check if date is valid by leap year
				'alertText' =>  __( '* Invalid date, must be in YYYY-MM-DD format', 'ipt_fsqm' )
			),
			'ipv4' => array(
				'regex' => "/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/",
				'alertText' =>  __( '* Invalid IP address', 'ipt_fsqm' )
			),
			'url' => array(
				'regex' => "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|\d|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.)+(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])*([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\x{E000}-\x{F8FF}]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/iu",
				'alertText' =>  __( '* Invalid URL', 'ipt_fsqm' )
			),
			'onlyNumberSp' => array(
				'regex' => "/^[0-9\ ]+$/",
				'alertText' =>  __( '* Numbers only', 'ipt_fsqm' )
			),
			'onlyLetterSp' => array(
				'regex' => "/^[a-zA-Z\ \']+$/",
				'alertText' =>  __( '* Letters only', 'ipt_fsqm' )
			),
			'onlyLetterNumber' => array(
				'regex' => "/^[0-9a-zA-Z]+$/",
				'alertText' =>  __( '* No spaces or special characters allowed', 'ipt_fsqm' )
			),
			'onlyLetterNumberSp' => array(
				'regex' => "/^[0-9a-zA-Z\ ]+$/",
				'alertText' =>  __( '* Only letters, number and spaces allowed', 'ipt_fsqm' )
			),
			'noSpecialCharacter' => array(
				'regex' => "/^[0-9a-zA-Z\ \.\,\?\\\"\']+$/",
				'alertText' => __( '* No special characters allowed', 'ipt_fsqm' ),
			),
			'personName' => array(
				'regex' => "/^[^\!\@\#\$\%\^\&\*\(\)\_\+\-\=\\\|\{\}\[\]\:\;\"\/\?\,\<\>\`\~1-9]+$/",
				'alertText' => __( 'Valid name only, no special characters except dots and single quote for salutation', 'ipt_fsqm' ),
			),
			//tls warning =>homegrown not fielded
			'dateFormat' => array(
				'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/",
				'alertText' =>  __( '* Invalid Date', 'ipt_fsqm' )
			),
			//tls warning =>homegrown not fielded
			'dateTimeFormat' => array(
				'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/",
				'alertText' =>  __( '* Invalid Date or Date Format', 'ipt_fsqm' ),
				'alertText2' =>  __( 'Expected Format => ', 'ipt_fsqm' ),
				'alertText3' =>  __( 'mm/dd/yyyy hh =>mm =>ss AM|PM or ', 'ipt_fsqm' ),
				'alertText4' =>  __( 'yyyy-mm-dd hh =>mm =>ss AM|PM', 'ipt_fsqm' )
			)
		);

		return $reg_exp;
	}

	protected function compat_data_mcq() {
		$data = array();
		foreach ( $this->data->mcq as $m_key => $mcq ) {
			if ( is_int( $m_key ) ) {
				$data[$m_key] = array(
					'type' => isset( $this->mcq[$m_key] ) ? $this->mcq[$m_key]['type'] : 'undefined',
					'm_type' => isset( $this->mcq[$m_key] ) ? $this->mcq[$m_key]['m_type'] : 'undefined',
					'options' => (array) $mcq,
					'others' => isset( $this->data->mcq[$m_key . '_others'] ) ? $this->data->mcq[$m_key . '_others'] : '',
				);
			} else {
				continue;
			}
		}
		$this->data->mcq = $data;
	}

	protected function compat_data_pinfo() {
		$data = array();
		$dbmap = array();
		foreach ( $this->data->pinfo as $p_key => $pinfo ) {
			if ( is_int( $p_key ) ) {
				$data[$p_key] = array(
					'type' => isset( $this->mcq[$p_key] ) ? $this->mcq[$p_key]['type'] : 'undefined',
					'm_type' => isset( $this->mcq[$p_key] ) ? $this->mcq[$p_key]['m_type'] : 'undefined',
				);
				switch ( $data[$p_key]['type'] ) {
				default :
					$data[$p_key]['value'] = (string) $pinfo;
				case 'p_radio' :
				case 'p_checkbox' :
					$data[$p_key]['options'] = (array) $pinfo;
					break;
				case 's_checkbox' :
					$data[$p_key]['value'] = true;
				}
			} else {
				$dbmap[$p_key] = $pinfo;
			}
		}

		//Now set the dbmap
		$keys = $this->get_keys_from_layouts_by_types( array( 'f_name', 'l_name', 'email', 'phone' ), $this->layout );
		if ( !empty( $keys ) ) {
			foreach ( $keys as $p_key ) {
				$data[$p_key] = array(
					'type' => isset( $this->pinfo[$p_key] ) ? $this->pinfo[$p_key]['type'] : 'undefined',
					'm_type' => isset( $this->pinfo[$p_key] ) ? $this->pinfo[$p_key]['m_type'] : 'undefined',
					'value' => $this->data->{$this->pinfo[$p_key]['type']},
				);
			}
		}

		$this->data->pinfo = $data;
	}

	protected function compat_data_freetype() {
		$data = array();
		foreach ( $this->data->freetype as $f_key => $freetype ) {
			$data[$f_key] = array(
				'type' => isset( $this->freetype[$f_key] ) ? $this->freetype[$f_key]['type'] : 'undefined',
				'm_type' => isset( $this->freetype[$f_key] ) ? $this->freetype[$f_key]['m_type'] : 'undefined',
				'value' => htmlspecialchars_decode( $freetype ),
			);
		}
		$this->data->freetype = $data;
	}

	public function prepare_empty_data() {
		$current_user = wp_get_current_user();
		$logged_in = is_user_logged_in() && $current_user instanceof WP_User;
		$this->data = new stdClass();
		$this->data->form_id = $this->form_id;
		$this->data->f_name = $logged_in ? $current_user->user_firstname != '' ? $current_user->user_firstname : $current_user->display_name : '';
		$this->data->l_name = $logged_in ? $current_user->user_lastname : '';
		$this->data->email = $logged_in ? $current_user->user_email : '';
		$this->data->phone = '';
		$this->data->ip = $this->settings['submission']['log_ip'] ? $_SERVER['REMOTE_ADDR'] : '';
		$this->data->star = 0;
		$this->data->score = 0;
		$this->data->max_score = 0;
		$this->data->date = current_time( 'mysql' );
		$this->data->comment = $this->settings['general']['default_comment'];
		$this->data->user_id = ( $logged_in && $this->settings['submission']['log_registered_user'] ) ? $current_user->ID : 0;

		$this->prepare_empty_data_mcq();
		$this->prepare_empty_data_freetype();
		$this->prepare_empty_data_pinfo();
	}

	protected function prepare_empty_data_mcq() {
		$this->data->mcq = array();
		//prepare the mcq
		foreach ( $this->mcq as $m_key => $mcq ) {
			$this->data->mcq[$m_key] = null;
		}
	}

	protected function prepare_empty_data_freetype() {
		$this->data->freetype = array();
		//prepare the freetype
		foreach ( $this->freetype as $f_key => $freetype ) {
			$this->data->freetype[$f_key] = null;
		}
	}

	protected function prepare_empty_data_pinfo() {
		$this->data->pinfo = array();
		//prepare the pinfo
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			$this->data->pinfo[$p_key] = null;
		}
	}

	/*==========================================================================
	 * Quick Preview, Email & Print APIs
	 *========================================================================*/
	public function get_admin_email_template( $msgs, $show_submission = false ) {
		if ( is_array( $msgs ) ) {
			$msgs = implode( "\n\n", $msgs );
		}
		ob_start();
		?>
		<?php if ( $this->settings['admin']['top_line'] == true ) : ?>
		<div class="block">
			 <!-- Start of preheader -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="preheader">
				<tbody>
				 <tr>
					<td width="100%">
						 <table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
							<tbody>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td align="right" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: #999999" st-content="preheader">
									 <?php printf( __( 'To see the full submission, please <a class="hlite" style="text-decoration: none; color: %2$s" href="%1$s">click here</a> (May require administrative access).', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $this->data_id ), $this->settings['email_template']['accent_bg'] ); ?>
								</td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- End of preheader -->
		</div>
		<?php endif; ?>
		<div class="block">
			 <!-- start of header -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
				<tbody>
				 <tr>
					<td>
						 <table width="960" bgcolor="<?php echo $this->settings['email_template']['accent_bg']; ?>" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hlitebg="edit" shadow="edit">
							<tbody>
							 <tr>
								<td>
									<?php if ( '' != $this->settings['admin']['email_logo'] ) : ?>
									<!-- logo -->
									<table width="450" cellpadding="0" cellspacing="0" border="0" align="left" class="devicewidth">
										<tbody>
										 <tr>
											<td valign="middle" width="270" style="padding: 10px 0 10px 20px;" class="logo">
												<div class="imgpop">
													<a href="<?php echo  $this->get_trackback_url(); ?>"><img src="<?php echo esc_attr( $this->settings['admin']['email_logo'] ); ?>" alt="logo" border="0" style="display:block; border:none; outline:none; text-decoration:none;" st-image="edit" class="logo"></a>
												</div>
											</td>
										 </tr>
										</tbody>
									</table>
									<!-- End of logo -->
									<?php endif; ?>
									 <!-- menu -->
									 <table width="450" cellpadding="0" cellspacing="0" border="0" align="right" class="devicewidth">
										<tbody>
										 <tr>
											<td width="450" valign="middle" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: <?php echo $this->settings['email_template']['accent_color']; ?>;line-height: 24px; padding: 10px 0;" align="right" class="menu" st-content="menu">
												<?php echo get_bloginfo( 'name' ); ?>
											</td>
											<td width="20"></td>
										 </tr>
										</tbody>
									 </table>
									 <!-- End of Menu -->
								</td>
							 </tr>
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- end of header -->
		</div>
		<div class="block">
			 <!-- image + text -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
				<tbody>
				 <tr>
					<td>
						<table bgcolor="#ffffff" width="960" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth" modulebg="edit">
							<tbody>
								<tr>
									<td width="100%" height="20"></td>
								</tr>
							 	<tr>
									<td>
										<table width="920" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
											<tbody>
												<?php if ( '' != $this->settings['theme']['logo'] ) : ?>
												 <tr>
													<!-- start of image -->
													<td align="center">
														 <img width="540" border="0" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="<?php echo $this->settings['theme']['logo']; ?>" class="bigimage" />
													</td>
												</tr>
												<!-- end of image -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
												<?php endif; ?>
												<!-- title -->
												<tr>
													<td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #333333; text-align:left;line-height: 20px;" st-title="rightimage-title">
														 <?php echo $this->name; ?>
													</td>
												</tr>
												<!-- end of title -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
												<!-- content -->
												<tr>
													<td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: #95a5a6; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
														<?php echo wptexturize( wpautop( $msgs ) ); ?>
													</td>
												</tr>
												<!-- end of content -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
											</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if ( $show_submission == true ) : ?>
		<div class="block">
			<table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
				<tbody>
					<tr>
						<td>
							<table bgcolor="#ffffff" width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" modulebg="edit">
								<tbody>
									<!-- Spacing -->
									<tr>
										<td width="100%" height="20"></td>
									</tr>
									<!-- Spacing -->
									<tr>
										<td>
											<?php $this->show_quick_preview( true, false ); ?>
										</td>
									</tr>
									<!-- Spacing -->
									<tr>
										<td width="100%" height="20"></td>
									</tr>
									<!-- Spacing -->
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
		<div class="block">
			 <!-- Start of preheader -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter">
				<tbody>
				 <tr>
					<td width="100%">
						 <table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
							<tbody>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td align="center" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: #999999" st-content="preheader">
									 <?php echo $this->settings['admin']['footer']; ?>
								</td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- End of preheader -->
		</div>
		<?php
		return ob_get_clean();
	}
	public function user_notification_email() {
		$format_string_components = $this->get_format_string();
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		ob_start();
		?>
		<?php if ( $this->settings['user']['top_line'] == true ) : ?>
		<div class="block">
			 <!-- Start of preheader -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="preheader">
				<tbody>
				 <tr>
					<td width="100%">
						 <table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
							<tbody>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td align="right" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: #999999" st-content="preheader">
									 <?php printf( __( 'If you can not read this email, please <a class="hlite" style="text-decoration: none; color: %2$s" href="%1$s">click here</a>.', 'ipt_fsqm' ), $this->get_trackback_url(), $this->settings['email_template']['accent_bg'] ); ?>
								</td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- End of preheader -->
		</div>
		<?php endif; ?>
		<div class="block">
			 <!-- start of header -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
				<tbody>
				 <tr>
					<td>
						 <table width="960" bgcolor="<?php echo $this->settings['email_template']['accent_bg']; ?>" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hlitebg="edit" shadow="edit">
							<tbody>
							 <tr>
								<td>
									<?php if ( '' != $this->settings['user']['email_logo'] ) : ?>
									<!-- logo -->
									<table width="450" cellpadding="0" cellspacing="0" border="0" align="left" class="devicewidth">
										<tbody>
										 <tr>
											<td valign="middle" width="270" style="padding: 10px 0 10px 20px;" class="logo">
												<div class="imgpop">
													<a href="<?php echo  $this->get_trackback_url(); ?>"><img src="<?php echo esc_attr( $this->settings['user']['email_logo'] ); ?>" alt="logo" border="0" style="display:block; border:none; outline:none; text-decoration:none;" st-image="edit" class="logo"></a>
												</div>
											</td>
										 </tr>
										</tbody>
									</table>
									<!-- End of logo -->
									<?php endif; ?>
									 <!-- menu -->
									 <table width="450" cellpadding="0" cellspacing="0" border="0" align="right" class="devicewidth">
										<tbody>
										 <tr>
											<td width="450" valign="middle" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: <?php echo $this->settings['email_template']['accent_color']; ?>;line-height: 24px; padding: 10px 0;" align="right" class="menu" st-content="menu">
												<?php echo $this->social_share_buttons( true ); ?>
											</td>
											<td width="20"></td>
										 </tr>
										</tbody>
									 </table>
									 <!-- End of Menu -->
								</td>
							 </tr>
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- end of header -->
		</div>
		<div class="block">
			 <!-- image + text -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
				<tbody>
				 <tr>
					<td>
						<table bgcolor="#ffffff" width="960" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth" modulebg="edit">
							<tbody>
								<tr>
									<td width="100%" height="20"></td>
								</tr>
							 	<tr>
									<td>
										<table width="920" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
											<tbody>
												<?php if ( '' != $this->settings['theme']['logo'] ) : ?>
												 <tr>
													<!-- start of image -->
													<td align="center">
														 <img width="540" border="0" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="<?php echo $this->settings['theme']['logo']; ?>" class="bigimage" />
													</td>
												</tr>
												<!-- end of image -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
												<?php endif; ?>
												<!-- title -->
												<tr>
													<td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #333333; text-align:left;line-height: 20px;" st-title="rightimage-title">
														 <?php echo $this->name; ?>
													</td>
												</tr>
												<!-- end of title -->
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
												<!-- content -->
												<tr>
													<td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: #95a5a6; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
														<?php echo str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( $this->settings['user']['notification_msg'] ) ); ?>
													</td>
												</tr>
												<!-- end of content -->
												<?php if ( $this->settings['user']['view_online'] == true ) : ?>
												<!-- Spacing -->
												<tr>
													<td width="100%" height="10"></td>
												</tr>
												<!-- button -->
												<tr>
													<td>
														 <table height="30" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" class="tablet-button" st-button="edit">
															<tbody>
															 <tr>
																<td width="auto" align="center" valign="middle" height="30" style=" background-color:<?php echo $this->settings['email_template']['accent_bg']; ?>; border-top-left-radius:4px; border-bottom-left-radius:4px;border-top-right-radius:4px; border-bottom-right-radius:4px; background-clip: padding-box;font-size:13px; font-family:Helvetica, arial, sans-serif; text-align:center;  color: <?php echo $this->settings['email_template']['accent_color']; ?>; font-weight: 300; padding-left:18px; padding-right:18px;">

																	 <span style="color: #ffffff; font-weight: 300;">
																		<a style="color: #ffffff; text-align:center;text-decoration: none;" href="<?php echo $this->get_trackback_url(); ?>"><?php echo $this->settings['user']['view_online_text']; ?></a>
																	 </span>
																</td>
															 </tr>
															</tbody>
														 </table>
													</td>
												</tr>
												<!-- /button -->
												<?php endif; ?>
												<!-- Spacing -->
												<tr>
													<td width="100%" height="20"></td>
												</tr>
												<!-- Spacing -->
											</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php if ( $this->settings['user']['show_submission'] == true ) : ?>
		<div class="block">
			<table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="bigimage">
				<tbody>
					<tr>
						<td>
							<table bgcolor="#ffffff" width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" modulebg="edit">
								<tbody>
									<!-- Spacing -->
									<tr>
										<td width="100%" height="20"></td>
									</tr>
									<!-- Spacing -->
									<tr>
										<td>
											<?php $this->show_quick_preview( true, false ); ?>
										</td>
									</tr>
									<!-- Spacing -->
									<tr>
										<td width="100%" height="20"></td>
									</tr>
									<!-- Spacing -->
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<div class="block">
			 <!-- Start of preheader -->
			 <table width="100%" bgcolor="#f6f4f5" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter">
				<tbody>
				 <tr>
					<td width="100%">
						 <table width="960" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
							<tbody>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td align="center" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 10px;color: #999999" st-content="preheader">
									 <?php echo $this->settings['user']['footer_msg']; ?>
								</td>
							 </tr>
							 <!-- Spacing -->
							 <tr>
								<td width="100%" height="5"></td>
							 </tr>
							 <!-- Spacing -->
							</tbody>
						 </table>
					</td>
				 </tr>
				</tbody>
			 </table>
			 <!-- End of preheader -->
		</div>
		<?php
		return ob_get_clean();
	}
	public function show_quick_preview( $for_email = false, $show_logo = true ) {
		global $ipt_fsqm_settings;
		$db_maps = array(
			'l_name' => __( 'Last Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
		);
		$user = null;
		if ( $this->data->user_id != 0 ) {
			$user = get_user_by( 'id', $this->data->user_id );
		}

		if ( $for_email ) {
			$this->email_styling = array(
				'th' => 'border-collapse: collapse; border: 1px solid #f6f4f5; color: #333; background: #fff; vertical-align: top; padding: 5px 5px 5px 10px; text-align: left; font-weight: normal;',
				'headth' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background-color: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: top; padding: 10px 0 10px 20px; text-align: left; font-weight: normal;',
				'footth' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background-color: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 10px 0 10px 20px; text-align: left; font-weight: normal;',
				'td' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; padding: 5px; text-align: left;',
				'tdc' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; padding: 5px; text-align: left; color: #519548; font-weight: bold;',
				'td_upload' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; padding: 5px; text-align: center;',
				'icons' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; text-align: center; width: 20px; padding: 5px; line-height: 1;',
				'iconshead' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; vertical-align: middle; text-align: center; width: 20px; padding: 5px; line-height: 1;',
				'th_icon' => 'border-collapse: collapse; background-color: transparent;',
				'td_center' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; text-align: center; padding: 5px;',
				'description' => 'display: block; font-size: 90%; color: #aaa; text-transform: lowercase; font-style: italic;',
				'descriptionhead' => 'display: block; font-size: 90%; color: #eee; text-transform: lowercase; font-style: italic;',
				'table' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; margin-top: 10px; margin: bottom: 10px; width: 100%; border-collapse: collapse; border: 0 none; background-color: #fff; color: #999; vertical-align: middle; text-align: left;',
				'inner_table' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; margin: 0px; width: 100%; border-collapse: collapse; border: 0 none; background-color: #fff; color: #999; vertical-align: top; text-align: left;',
				'tr' => 'border-collapse: collapse; border: 1px solid #f6f4f5; vertical-align: middle; padding: 0;',
				'headtr' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
				'foottr' => 'border-collapse: collapse; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
				'thead' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
				'tfoot' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid ' . $this->settings['email_template']['accent_bg'] . '; background: ' . $this->settings['email_template']['accent_bg'] . '; color: ' . $this->settings['email_template']['accent_color'] . '; vertical-align: middle; padding: 0;',
				'tbody' => 'border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-weight: normal; border: 1px solid #f6f4f5; color: #999; vertical-align: middle; padding: 0;',
				'logo_container' => 'margin: 10px auto 20px; text-align: center;',
				'logo' => 'border: 0 none; max-width: 100%; height: auto;',
			);
			$this->email_styling = apply_filters( 'ipt_fsqm_form_elements_quick_preview_email_style', $this->email_styling, $this );
		}

		$format_string_components = $this->get_format_string();
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );

		// Loop through all labels and apply the filter to show latex
		$this->apply_label_filters();
?>
<?php if ( '' != $this->settings['theme']['logo'] && $show_logo ) : ?>
	<div class="ipt_fsqm_form_logo" style="<?php echo esc_attr( $this->email_styling['logo_container'] ); ?>">
		<img style="<?php echo esc_attr( $this->email_styling['logo'] ); ?>" src="<?php echo esc_attr( $this->settings['theme']['logo'] ); ?>" alt="<?php echo esc_attr( $this->name ); ?>">
	</div>
<?php endif; ?>
<?php if ( '' != $this->settings['summary']['before'] ) : ?>
<table class="ipt_fsqm_preview devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wptexturize( wpautop( $this->settings['summary']['before'] ) ); ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>
<table class="ipt_fsqm_preview devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<thead style="<?php echo $this->email_styling['thead']; ?>">
		<tr style="<?php echo $this->email_styling['headtr']; ?>">
			<th style="<?php echo $this->email_styling['headth']; ?>" scope="col" colspan="2"><?php echo $this->name; ?> ~ #<?php echo str_pad( $this->data_id, 10, '0', STR_PAD_LEFT ); ?> | <?php printf( __( 'On %s', 'ipt_fsqm' ), date_i18n( get_option( 'date_format' ) . __( ' \a\t ', 'ipt_fsqm' ) . get_option( 'time_format' ), strtotime( $this->data->date ) ) ); ?></th>
		</tr>
	</thead>
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<?php if ( true == $this->settings['summary']['f_name'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'First Name', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $this->data->f_name; ?></td>
		</tr>
		<?php endif; ?>
		<?php foreach ( $db_maps as $key => $label ) : ?>
		<?php if ( $this->data->{$key} != '' && true == $this->settings['summary'][$key] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $label; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $key == 'email' ? '<a href="mailto:' . $this->data->{$key} . '">' . $this->data->{$key} . '</a>' : $this->data->{$key}; ?></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( true == $this->settings['summary']['ip'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'IP Address', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $this->data->ip; ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $this->data->max_score != 0 ) : ?>
		<?php if ( true == $this->settings['summary']['total_score'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['summary']['tscore_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php printf( __( '%1$s out of %2$s (%3$s%%)', 'ipt_fsqm' ), $this->data->score, $this->data->max_score, number_format_i18n( $this->data->score * 100 / $this->data->max_score, 2 ) ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['designation'] ) : ?>
		<?php if ( $this->settings['ranking']['enabled'] == true ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th rowspan="<?php echo ( $format_string_components['%DESIGNATIONMSG%'] != '' ? '2' : '1' ); ?>" style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['ranking']['title'] ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $format_string_components['%DESIGNATION%']; ?></td>
		</tr>
		<?php if ( $format_string_components['%DESIGNATIONMSG%'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo $format_string_components['%DESIGNATIONMSG%']; ?></td>
		</tr>
		<?php endif; ?>
		<?php endif; ?>
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( $this->settings['general']['comment_title'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php echo $this->settings['general']['comment_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wpautop( $this->data->comment ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['user_account'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'User Account', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php if ( $this->data->user_id != 0 && $user instanceof WP_User ) : ?>
				<?php if ( is_admin() ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ipt_fsqm_view_all_submissions&user_id=' . $this->data->user_id ) ); ?>"><?php echo $user->display_name; ?></a>
				<?php else : ?>
				<a href="<?php echo $this->get_utrackback_url(); ?>"><?php echo $user->display_name; ?></a>
				<?php endif; ?>
				<?php else : ?>
				<?php _e( 'Guest', 'ipt_fsqm' ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( true == $this->settings['summary']['link'] ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'Link', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<a href="<?php echo $this->get_trackback_url(); ?>"><?php echo $this->get_trackback_url(); ?></a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( '0' != $this->settings['general']['terms_page'] || !empty( $this->settings['general']['terms_page'] ) ) : $link = get_permalink( $this->settings['general']['terms_page'] ); ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<th style="<?php echo $this->email_styling['th']; ?>" scope="row"><?php _e( 'Accepted Terms & Conditions', 'ipt_fsqm' ); ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( $for_email ) : ?>
		<tr style="border: 0 none;">
			<td height="20" style="border: 0 none;" colspan="2"></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
<?php if ( '' != $this->settings['summary']['after'] ) : ?>
<table class="ipt_fsqm_preview devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['td']; ?>"><?php echo wptexturize( wpautop( $this->settings['summary']['after'] ) ); ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<table class="ipt_fsqm_preview devicewidthinner" style="<?php echo $this->email_styling['table']; ?>" width="100%">
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
<?php foreach ( $this->layout as $layout_key => $layout ) : ?>
	<?php
	if ( $this->validate_data_against_conditional_logic( $layout, $layout_key ) == false ) {
		continue;
	}
	?>
		<tr style="<?php echo $this->email_styling['thead']; ?>" class="head">
			<th style="<?php echo $this->email_styling['headth']; ?>" colspan="2"><?php echo $layout['title']; ?></th>
			<th style="<?php echo $this->email_styling['iconshead']; ?>" class="icons">
				<?php
				if ( isset( $layout['icon'] ) ) {
					$container_image = $ui->get_icon_image_name( $layout['icon'] );
					if ( $container_image !== false ) {
						echo '<img src="' . $this->icon_path . $container_image . '" height="16" width="16" style="' . $this->email_styling['th_icon'] . '" />';
					}
				}
				?>
			</th>
			<th style="<?php echo $this->email_styling['headth']; ?> border-left: 0 none;" colspan="2"><span class="description" style="<?php echo $this->email_styling['descriptionhead']; ?>"><?php echo $layout['subtitle']; ?></span></th>
		</tr>
		<?php if ( $layout['description'] != '' ) : ?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td colspan="5" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo apply_filters( 'ipt_uif_richtext', $layout['description'] ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php $this->populate_layout( $layout_key, $layout ); ?>
<?php endforeach; ?>
	</tbody>
</table>
		<?php
	}

	public function populate_layout( $layout_key, $layout ) {
?>
		<?php foreach ( (array) $layout['elements'] as $l_key => $layout_element ) : ?>
		<?php
			$element = $layout_element['type'];
			$key = $layout_element['key'];
			$element_data = $this->get_element_from_layout( $layout_element );
			$submission_data = $this->get_submission_from_data( $layout_element );

			if ( $layout_element['m_type'] == 'design' ) {
				// Check for conditional logic
				if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
					continue;
				}

				// At this point, so conditional checks out
				$child_element = $this->get_element_from_layout( $layout_element );
				if ( isset( $child_element['elements'] ) && is_array( $child_element['elements'] ) ) {
					$this->populate_layout( $l_key, $child_element );
				}

				// No need to check any further
				continue;
			}

			// Don't show if conditional logic returns false
			if ( false === $this->validate_data_against_conditional_logic( $element_data, $key ) ) {
				continue;
			}
?>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<?php $this->build_element_html( $element, $key, $element_data, $submission_data, 'ipt_fsqm_form_' . $this->form_id ); ?>
		</tr>
		<?php endforeach; ?>
		<?php
	}

	public function apply_label_filters() {
		if ( ! empty( $this->mcq ) && is_array( $this->mcq ) ) :
		foreach ( $this->mcq as $e_key => $element ) {
			$this->mcq[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->mcq[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->mcq[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} else {
						$this->mcq[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['rows'] ) && is_array( $element['settings']['rows'] ) ) {
				foreach ( $element['settings']['rows'] as $r_key => $row ) {
					$this->mcq[$e_key]['settings']['rows'][$r_key] = apply_filters( 'ipt_uif_label', $row );
				}
			}
			if ( isset( $element['settings'] ) && isset( $element['settings']['columns'] ) && is_array( $element['settings']['columns'] ) ) {
				foreach ( $element['settings']['columns'] as $r_key => $row ) {
					$this->mcq[$e_key]['settings']['columns'][$r_key] = apply_filters( 'ipt_uif_label', $row );
				}
			}
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->feedback ) && is_array( $this->feedback ) ) :
		foreach ( $this->feedback as $e_key => $element ) {
			$this->feedback[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->feedback[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->design ) && is_array( $this->design ) ) :
		foreach ( $this->design as $e_key => $element ) {
			$this->design[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->design[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
		}
		unset( $e_key, $element );
		endif;

		if ( ! empty( $this->pinfo ) && is_array( $this->pinfo ) ) :
		foreach ( $this->pinfo as $e_key => $element ) {
			$this->pinfo[$e_key]['title'] = apply_filters( 'ipt_uif_label', $element['title'] );
			$this->pinfo[$e_key]['subtitle'] = apply_filters( 'ipt_uif_label', $element['subtitle'] );
			if ( isset( $element['settings'] ) && isset( $element['settings']['options'] ) && is_array( $element['settings']['options'] ) ) {
				foreach ( $element['settings']['options'] as $o_key => $op ) {
					if ( is_array( $op ) ) {
						if ( isset( $op['label'] ) ) {
							$this->pinfo[$e_key]['settings']['options'][$o_key]['label'] = apply_filters( 'ipt_uif_label', $op['label'] );
						}
					} else {
						$this->pinfo[$e_key]['settings']['options'][$o_key] = apply_filters( 'ipt_uif_label', $op );
					}
				}
			}
		}
		endif;
	}

	public function social_share_buttons( $use_image = false ) {
		if ( $this->settings['social']['show'] == false ) {
			return '';
		}
		$op = $this->settings['social'];
		$standalone_url = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $this->form_id );
		$format_string = $this->get_format_string();
		$op['image'] = str_replace( '%LOGO%', $this->settings['theme']['logo'], $op['image'] );
		$op['url'] = str_replace( '%SELF%', $standalone_url['url'], $op['url'] );
		$op['title'] = str_replace( '%NAME%', $this->name, $op['title'] );
		$op['description'] = str_replace( array_keys( $format_string ), array_values( $format_string ), $op['description'] );
		$op = array_map( 'trim', $op );
		$op = array_map( 'urlencode', $op );

		// Facebook
		if ( $op['facebook_app'] == '' ) {
			$facebook_url = 'https://www.facebook.com/sharer.php?s=100&p[url]=' . $op['url'] . '&p[title]=' . $op['title'];
			if ( $op['image'] != '' ) {
				$facebook_url .= '&p[images][0]=' . $op['image'];
			}
			if ( $op['description'] != '' ) {
				$facebook_url .= '&p[summary]=' . $op['description'];
			}
		} else {
			$facebook_url = 'https://www.facebook.com/dialog/feed?app_id=' . $op['facebook_app'] . '&' .
							'link=' . $op['url'] . '&name=' . $op['title'] . '&redirect_uri=' . $op['fb_url'];
			if ( $op['image'] != '' ) {
				$facebook_url .= '&picture=' . $op['image'];
			}
			if ( $op['description'] != '' ) {
				$facebook_url .= '&description=' . $op['description'];
			}
		}



		// Twitter
		$twitter_url = 'https://twitter.com/share?url=' . $op['url'] . '&text=' . $op['title'];
		if ( $op['twitter_via'] != '' ) {
			$twitter_url .= '&via=' . $op['twitter_via'];
		}
		if ( $op['twitter_hash'] != '' ) {
			$twitter_url .= '&hashtags=' . $op['twitter_hash'];
		}

		// Google+
		$google_url = 'https://plus.google.com/share?url=' . $op['url'];

		// Pinterest
		if ( $op['image'] != '' ) {
			$pinterest_url = 'https://pinterest.com/pin/create/bookmarklet/?media=' . $op['image'] . '&url=' . $op['url'] . '&description=' . $op['title'];
		}

		$html = array(
			'facebook_url' => '<i class="ipticm ipt-icomoon-facebook3"></i>',
			'twitter_url' => '<i class="ipticm ipt-icomoon-twitter3"></i>',
			'google_url' => '<i class="ipticm ipt-icomoon-googleplus3"></i>',
		);
		if ( $op['image'] != '' ) {
			$html['pinterest_url'] = '<i class="ipticm ipt-icomoon-pinterest2"></i>';
		}
		if ( $use_image ) {
			$html = array(
				'facebook_url' => '<img src="' . plugins_url( '/lib/images/icomoon/fff/PNG/facebook3.png', IPT_FSQM_Loader::$abs_file ) . '" alt="Facebook" />',
				'twitter_url' => '<img src="' . plugins_url( '/lib/images/icomoon/fff/PNG/twitter3.png', IPT_FSQM_Loader::$abs_file ) . '" alt="Twitter" />',
				'google_url' => '<img src="' . plugins_url( '/lib/images/icomoon/fff/PNG/googleplus3.png', IPT_FSQM_Loader::$abs_file ) . '" alt="Google Plus" />',
			);
			if ( $op['image'] != '' ) {
				$html['pinterest_url'] = '<img src="' . plugins_url( '/lib/images/icomoon/fff/PNG/pinterest2.png', IPT_FSQM_Loader::$abs_file ) . '" alt="Pinterest" />';
			}
		}
		$return = '';
		foreach ( $html as $key => $val ) {
			$return .= '<a href="' . ${$key} . '" target="_blank">' . $val . '</a>&nbsp;&nbsp;';
		}
		return apply_filters( 'ipt_fsqm_filter_social_buttons', $return, $html, $this->settings['social'], $this->form_id, $this->data_id );
	}

	/*==========================================================================
	 * DEFAULT ELEMENTS - OVERRIDE
	 *========================================================================*/

	public function build_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_slider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_slider( $element_data['title'], $element_data['subtitle'], $submission_data, $element_data['description'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], true );
	}

	public function build_range( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_range( $element_data['title'], $element_data['subtitle'], $submission_data, $element_data['description'], $element_data['settings']['prefix'], $element_data['settings']['suffix'], true );
	}

	public function build_spinners( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$rowspan = count( $element_data['settings']['options'] );
		$tr = false;
?>
		<th style="<?php echo $this->email_styling['th']; ?>" rowspan="<?php echo $rowspan; ?>" scope="row" colspan="2">
			<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
			<?php if ( $element_data['description'] !== '' ) : ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
			</div>
			<?php endif; ?>
		</th>
		<?php foreach ( $element_data['settings']['options'] as $o_key => $option ) : ?>
		<?php
		// Compatibility with v-2.5.0
		if ( ! is_array( $option ) ) {
			$option = array(
				'label' => $option,
			);
		}
		?>
		<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
		<?php $this->make_slider_inner( $option['label'], $submission_data['options'][$o_key] ); ?>
		<?php $tr = true; ?>
		<?php endforeach; ?>
		<?php
	}

	public function build_grading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$rowspan = count( $element_data['settings']['options'] );
		$score = $this->settings['summary']['individual_score'] && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] );
		if ( $score ) {
			$rowspan++;
		}
		$tr = false;
?>
		<th style="<?php echo $this->email_styling['th']; ?>" rowspan="<?php echo $rowspan; ?>" scope="row" colspan="2">
			<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
			<?php if ( $element_data['description'] !== '' ) : ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
			</div>
			<?php endif; ?>
		</th>
		<?php foreach ( $element_data['settings']['options'] as $o_key => $option ) : ?>
		<?php // backward compatibility -2.4.0
		if ( !is_array( $option ) ) {
			$option = array(
				'label' => $option,
				'prefix' => '',
				'suffix' => '',
			);
		}
		?>
		<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
		<?php if ( $element_data['settings']['range'] == true ) : ?>
		<?php $this->make_range_inner( $option['label'], $submission_data['options'][$o_key], $option['prefix'], $option['suffix'] ); ?>
		<?php else : ?>
		<?php $this->make_slider_inner( $option['label'], $submission_data['options'][$o_key], $option['prefix'], $option['suffix'] ); ?>
		<?php endif; ?>
		<?php $tr = true; ?>
		<?php endforeach; ?>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function build_starrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_scalerating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, 'scale' );
	}

	public function build_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		// Shortcut variables
		$rows = $element_data['settings']['rows'];
		$columns = $element_data['settings']['columns'];
		$values = $submission_data['rows'];
		$multiple = $element_data['settings']['multiple'];

		// Paths to icon
		if ( $multiple ) {
			$checked = '<img src="' . $this->icon_path . 'checkbox-checked.png" height="16" width="16" />';
			$unchecked = '<img src="' . $this->icon_path . 'checkbox-unchecked.png" height="16" width="16" />';
		} else {
			$checked = '<img src="' . $this->icon_path . 'radio-checked.png" height="16" width="16" />';
			$unchecked = '<img src="' . $this->icon_path . 'radio-unchecked.png" height="16" width="16" />';
		}

		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}
		$rowspans = 1;
		$score = $this->settings['summary']['individual_score'] && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0;
		if ( $score ) {
			$rowspans += 1;
		}
?>
<th rowspan="<?php echo $rowspans; ?>" colspan="2" style="<?php echo $this->email_styling['th']; ?>">
	<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
	<?php if ( $element_data['description'] !== '' ) : ?>
	<div class="ipt_uif_richtext">
		<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
	</div>
	<?php endif; ?>
</th>
<td style="<?php echo $this->email_styling['td']; ?> padding: 0;" colspan="3" class="matrix">
<table style="<?php echo $this->email_styling['inner_table']; ?>">
	<thead style="<?php echo $this->email_styling['thead']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
		<tr style="<?php echo $this->email_styling['tr']; ?> border-top: 0 none; border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-left: 0 none;" scope="col"></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<th style="<?php echo $this->email_styling['th']; ?> border-top: 0 none; border-right: 0 none;" scope="col">
				<?php echo $column; ?>
				<?php if ( $score && isset( $element_data['settings']['scores'] ) && is_array( $element_data['settings']['scores'] ) && isset( $element_data['settings']['scores'][$c_key] ) && '' != trim( $element_data['settings']['scores'][$c_key] ) ) : ?>
				<br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php printf( __( 'Score: %s', 'ipt_fsqm' ), $element_data['settings']['scores'][$c_key] ); ?></span>
				<?php endif; ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tfoot style="<?php echo $this->email_styling['tfoot']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
		<tr style="<?php echo $this->email_styling['tr']; ?> border-bottom: 0 none; border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-left: 0 none;" scope="col"></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<th style="<?php echo $this->email_styling['th']; ?> border-bottom: 0 none; border-right: 0 none;" scope="col">
				<?php echo $column; ?>
				<?php if ( $score && isset( $element_data['settings']['scores'] ) && is_array( $element_data['settings']['scores'] ) && isset( $element_data['settings']['scores'][$c_key] ) && '' != trim( $element_data['settings']['scores'][$c_key] ) ) : ?>
				<br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php printf( __( 'Score: %s', 'ipt_fsqm' ), $element_data['settings']['scores'][$c_key] ); ?></span>
				<?php endif; ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</tfoot>
	<tbody style="<?php echo $this->email_styling['tbody']; ?>">
		<?php foreach ( $rows as $r_key => $row ) : ?>
		<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
			$values[$r_key] = (array) $values[$r_key];
		}
?>
		<tr style="<?php echo $this->email_styling['tr']; ?> border-left: 0 none; border-right: 0 none;">
			<th style="<?php echo $this->email_styling['th']; ?> border-left: 0 none;" scope="row"><?php echo $row; ?></th>
			<?php foreach ( $columns as $c_key => $column ) : ?>
			<?php
			?>
			<td style="<?php echo $this->email_styling['td_center']; ?> border-right: 0 none;" class="icons_matrix">
				<?php if ( in_array( (string) $c_key, $values[$r_key], true ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</td>
<?php if ( $score ) : ?>
</tr>
<tr style="<?php echo $this->email_styling['tr']; ?>">
	<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
	<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
	<td style="<?php echo $this->email_styling['td']; ?>">
		<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
	</td>
<?php endif; ?>
		<?php
	}

	public function build_toggle( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_feedback_large( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_feedback_small( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_upload( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$uploader = new IPT_FSQM_Form_Elements_Uploader( $this->form_id, $key );
		$uploads = $uploader->get_uploads( $this->data_id );
		$rowspan = count( $uploads );
		if ( $rowspan < 1 ) {
			$rowspan = 1;
		}
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		$new_image = $ui->get_icon_image_name( $element_data['settings']['icon'] );
		if ( false === $new_image ) {
			$img = '';
		} else {
			$img = '<img src="' . $this->icon_path . $new_image . '" height="16" width="16" />';
		}
		?>
		<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="<?php echo $rowspan; ?>">
			<?php echo $element_data['title']; ?><br />
			<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
			<?php if ( $element_data['description'] !== '' ) : ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
			</div>
			<?php endif; ?>
		</th>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $img; ?>
		</td>
		<?php if ( empty( $uploads ) ) : ?>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php _e( 'No files uploaded.', 'ipt_fsqm' ); ?>
		</td>
		<?php else : ?>
			<?php $tr = false; ?>
			<?php foreach ( $uploads as $upload ) : ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<?php if ( $tr ) : ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<?php endif; ?>
			<td style="<?php echo $this->email_styling['td_upload']; ?>" colspan="2" class="upload_td">
				<?php if ( '' == $upload['guid'] ) : ?>
					<?php _e( 'Deleted', 'ipt_fsqm' ); ?>
				<?php else : ?>
				<a href="<?php echo $upload['guid']; ?>" target="_blank" title="<?php echo esc_attr( $upload['filename'] ); ?>">
					<?php if ( $upload['thumb_url'] != '' ) : ?>
					<img src="<?php echo $upload['thumb_url']; ?>" alt="<?php echo esc_attr( $upload['filename'] ); ?>" /> <br/>
					<?php endif; ?>
					<?php echo $upload['name'] . ' (' . $upload['mime_type'] . ')'; ?>
				</a>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php
	}

	public function build_mathematical( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$img = '';
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			$new_image = $ui->get_icon_image_name( $element_data['settings']['icon'] );
			if ( false === $new_image ) {
				$img = '';
			} else {
				$img = '<img src="' . $this->icon_path . $new_image . '" height="16" width="" />';
			}
		}
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="1">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $element_data['settings']['prefix']; ?>
				<?php echo $img; ?>
				<strong style="font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;"><?php echo number_format_i18n( (float) $submission_data['value'], $element_data['settings']['precision'] ); ?></strong>
				<?php echo $element_data['settings']['suffix']; ?>
			</td>
		<?php
	}

	public function build_f_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_l_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_textinput( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_textarea( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_password( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_p_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_p_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	public function build_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_address( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$address = '<img src="' . $this->icon_path . 'address-book.png" height="16" width="16" />';
		$recipient = '<img src="' . $this->icon_path . 'users.png" height="16" width="16" />';
		$flag = '<img src="' . $this->icon_path . 'flag.png" height="16" width="16" />';
?>
			<th style="<?php echo $this->email_styling['th']; ?>" rowspan="5" scope="row" colspan="2">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo $recipient; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $submission_data['values']['recipient']; ?>
			</td>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo $address; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $submission_data['values']['line_one']; ?>
			</td>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo $address; ?>
			</td>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['values']['line_two']; ?>
			</td>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo $address; ?>
			</td>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['values']['line_three']; ?>
			</td>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo $flag; ?>
			</td>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['values']['country']; ?>
			</td>
		<?php
	}

	public function build_keypad( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_datetime( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$date_formats = array(
			'yy-mm-dd' => 'Y-m-d',
			'mm/dd/yy' => 'm/d/Y',
			'dd.mm.yy' => 'd.m.Y',
			'dd-mm-yy' => 'd-m-Y',
		);
		$time_formats = array(
			'HH:mm:ss' => 'H:i:s',
			'hh:mm:ss TT' => 'h:i:s A',
		);
		$value = $submission_data['value'];
		$current_picker_timestamp = strtotime( $value );
		if ( $current_picker_timestamp != false ) {
			switch ( $element_data['settings']['type'] ) {
			case 'date' :
				$value = date( $date_formats[$element_data['settings']['date_format']], $current_picker_timestamp );
				break;
			case 'time' :
				$value = date( $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			case 'datetime' :
				$value = date( $date_formats[$element_data['settings']['date_format']] . ' ' . $time_formats[$element_data['settings']['time_format']], $current_picker_timestamp );
				break;
			}
		}
		$submission_data['value'] = $value;
		$this->make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context );
	}

	public function build_p_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, false );
	}

	/*==========================================================================
	 * Internal helper methods - Also made public
	 *========================================================================*/
	public function make_mcqs( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $score = true ) {
		$rowspans = count( $element_data['settings']['options'] );
		if ( $this->settings['summary']['hide_options'] == true ) {
			$rowspans = count( $submission_data['options'] );
			if ( $rowspans == 0 ) {
				$rowspans = 1;
			}
		}
		if ( $element_data['settings']['others'] == 'true' && ( ( $this->settings['summary']['hide_options'] == true && in_array( 'others', $submission_data['options'] ) ) || $this->settings['summary']['hide_options'] != true ) ) {
			$rowspans += 1;
		}
		if ( in_array( 'others', $submission_data['options'] ) ) {
			$rowspans += 1;
		}
		switch ( $element_data['type'] ) {
		case 'select' :
		case 'p_select' :
		case 'radio' :
		case 'p_radio' :
			$checked = '<img src="' . $this->icon_path . 'radio-checked.png" height="16" width="16" />';
			$unchecked = '<img src="' . $this->icon_path . 'radio-unchecked.png" height="16" width="16" />';
			break;
		default :
			$checked = '<img src="' . $this->icon_path . 'checkbox-checked.png" height="16" width="16" />';
			$unchecked = '<img src="' . $this->icon_path . 'checkbox-unchecked.png" height="16" width="16" />';
		}

		$score = $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0;
		if ( $score ) {
			$rowspans += 1;
		}
		$all_scores = array();
		$correct_ops = array();
		if ( $this->settings['summary']['highlight_correct'] == true && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0 ) {
			foreach ( $element_data['settings']['options'] as $o_key => $op ) {
				$all_scores[$o_key] = (float) $op['score'];
			}
			unset($o_key, $op);
			$maxp_score = max( $all_scores );
			$correct_ops = array_keys( $all_scores, $maxp_score, true );
		}

		$tr = false;
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" rowspan="<?php echo $rowspans; ?>" scope="row">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<?php foreach ( $element_data['settings']['options'] as $o_key => $op ) : ?>
			<?php if ( $this->settings['summary']['hide_options'] == true && ! in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
			<?php continue; ?>
			<?php endif; ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php if ( in_array( (string) $o_key, $submission_data['options'], true ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php if ( $this->settings['summary']['highlight_correct'] == true && in_array( (int) $o_key, $correct_ops ) ) : ?>
			<td style="<?php echo $this->email_styling['tdc']; ?>" colspan="2">
			<?php else : ?>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php endif; ?>
				<?php echo $op['label']; ?>
				<?php if ( $score && trim( $op['score'] ) != '' ) : ?>
				<br /><span class="description" style="<?php echo $this->email_styling['description']; ?>">(<?php echo __( 'Score', 'ipt_fsqm' ) . ' ' . $op['score']; ?>)</span>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php if ( $element_data['settings']['others'] == 'true' && ( ( $this->settings['summary']['hide_options'] == true && in_array( 'others', $submission_data['options'] ) ) || $this->settings['summary']['hide_options'] != true ) ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php if ( in_array( 'others', $submission_data['options'] ) ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $element_data['settings']['o_label']; ?>
			</td>
		<?php endif; ?>
		<?php if ( in_array( 'others', $submission_data['options'] ) ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>">
				<?php echo '<img src="' . $this->icon_path . 'pencil.png" height="16" width="16" />'; ?>
			</td>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>"><?php echo $submission_data['others']; ?></td>
		<?php endif; ?>
		<?php if ( $this->settings['summary']['hide_options'] == true && 0 == count( $submission_data['options'] ) ) : ?>
		<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
			<?php echo $unchecked; ?>
		</td>
		<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
			<?php _e( 'Unattempted', 'ipt_fsqm' ); ?>
		</td>
		<?php endif; ?>
		<?php if ( $score ) : ?>
		</tr><tr style="<?php echo $this->email_styling['tr']; ?>">
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>"><?php echo $this->score_img; ?></td>
			<th colspan="1" style="<?php echo $this->email_styling['th']; ?>"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['scoredata']['score']; ?>/<?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function make_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$checked = '<img src="' . $this->icon_path . 'checkbox-checked.png" height="16" width="16" />';
		$unchecked = '<img src="' . $this->icon_path . 'checkbox-unchecked.png" height="16" width="16" />';
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td style="<?php echo $this->email_styling['icons']; ?>" colspan="1" class="icons">
				<?php if ( true == $submission_data['value'] ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
			</td>
			<?php if ( $element_data['type'] == 'toggle' ) : ?>
			<td colspan="2" style="<?php echo $this->email_styling['td']; ?>">
				<?php if ( true == $submission_data['value'] ) : ?>
				<?php echo $element_data['settings']['on']; ?>
				<?php else : ?>
				<?php echo $element_data['settings']['off']; ?>
				<?php endif; ?>
			</td>
			<?php else : ?>
			<td colspan="2"></td>
			<?php endif; ?>
		<?php
	}

	public function make_texts( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$img = '<img src="' . $this->icon_path . 'pencil2.png" height="16" width="16" />';
		$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		if ( isset( $element_data['settings'] ) && isset( $element_data['settings']['icon'] ) ) {
			$new_image = $ui->get_icon_image_name( $element_data['settings']['icon'] );
			if ( false === $new_image ) {
				$img = '';
			} else {
				$img = '<img src="' . $this->icon_path . $new_image . '" height="16" width="16" />';
			}
		} else {
			$new_image = '';
			switch ( $element_definition['type'] ) {
				case 'f_name' :
				case 'l_name' :
				case 'p_name' :
					$new_image = 'user4.png';
					break;
				case 'email' :
				case 'p_email' :
					$new_image = 'mail2.png';
					break;
				case 'phone' :
				case 'p_phone' :
					$new_image = 'mobile.png';
			}

			if ( $new_image != '' ) {
				$img = '<img src="' . $this->icon_path . $new_image . '" height="16" width="16" />';
			}
		}
		$show_score = false;
		if ( $element_definition['type'] == 'feedback_large' || $element_definition['type'] == 'feedback_small' ) {
			if ( $this->settings['summary']['individual_score'] && '' != $element_data['settings']['score'] && is_numeric( $element_data['settings']['score'] ) ) {
				$show_score = true;
			}
		}
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="<?php echo ( $show_score ? '2' : '1' ); ?>">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo wpautop( esc_textarea( $submission_data['value'] ) ); ?>
			</td>
			<?php if ( $show_score ) : ?>
		</tr>
		<tr>
			<td class="icons" style="<?php echo $this->email_styling['icons']; ?>"><?php echo $this->score_img; ?></td>
			<th colspan="1" style="<?php echo $this->email_styling['th']; ?>"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo ( $submission_data['score'] == '' ? __( 'Unassigned', 'ipt_fsqm' ) : $submission_data['score'] ); ?>/<?php echo $element_data['settings']['score']; ?>
			</td>
			<?php endif; ?>
		<?php
	}

	public function make_sortings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $score = true ) {
		if ( empty( $submission_data['order'] ) ) {
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row">
				<?php echo $element_data['title']; ?><br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?><br />
				<?php if ( $score && trim( $element_data['settings']['base_score'] ) != '' ) : ?>
				<?php echo __( 'Base Score: ', 'ipt_fsqm' ) . $element_data['settings']['base_score']; ?>
				<?php endif; ?>
				</span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td class="icons">
				<?php echo '<img src="' . $this->icon_path . 'close.png" height="16" width="16" />'; ?>
			</td>
			<td colspan="2">
				<?php _e( 'N/A', 'ipt_fsqm' ); ?>
			</td>
			<?php
			return;
		}
		$rowspans = count( $element_data['settings']['options'] );
		$checked = '<img src="' . $this->icon_path . 'checkmark.png" height="16" width="16" />';
		$unchecked = '<img src="' . $this->icon_path . 'close.png" height="16" width="16" />';
		$point = '<img src="' . $this->icon_path . 'point-right.png" height="16" width="16" />';
		$correct_keys = array_keys( $element_data['settings']['options'] );
		$score = $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] ) && $submission_data['scoredata']['max_score'] != 0;
		if ( $score ) {
			$rowspans += 1;
		}
		$tr = false;
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" rowspan="<?php echo $rowspans; ?>" scope="row">
				<?php echo $element_data['title']; ?><br />
				<span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?>
				<?php if ( $score && trim( $element_data['settings']['base_score'] ) != '' ) : ?>
				<?php echo '<br />' . __( 'Base Score: ', 'ipt_fsqm' ) . $element_data['settings']['base_score']; ?>
				<?php endif; ?>
				</span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<?php foreach ( $submission_data['order'] as $o_pos => $o_key ) : ?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php if ( $score ) : ?>
				<?php if ( (string) $correct_keys[$o_pos] == $o_key ) : ?>
				<?php echo $checked; ?>
				<?php else : ?>
				<?php echo $unchecked; ?>
				<?php endif; ?>
				<?php else : ?>
				<?php echo $point; ?>
				<?php endif; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $element_data['settings']['options'][$o_key]['label']; ?>
				<?php if ( $score ) : ?>
				<br /><span class="description" style="<?php echo $this->email_styling['description']; ?>">(<?php echo __( 'Correct Position:', 'ipt_fsqm' ) . ' ' . ( array_search( $o_key, $correct_keys ) + 1 ); ?><?php if ( trim( $element_data['settings']['options'][$o_key]['score'] ) != '' && $element_data['settings']['score_type'] == 'individual' ) echo ', ' . __( 'Score:', 'ipt_fsqm' ) . ' ' . $element_data['settings']['options'][$o_key]['score']; ?>)</span>
				<?php endif; ?>
			</td>
			<?php $tr = true; ?>
			<?php endforeach; ?>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}

	public function make_ratings( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context, $icon = 'star' ) {
		$rowspan = count( $element_data['settings']['options'] );
		if ( $icon == 'star' ) {
			$fullstar = '<img src="' . $this->icon_path . 'star.png" height="16" width="16" />';
			$emptystar = '<img src="' . $this->icon_path . 'star2.png" height="16" width="16" />';
		} else {
			$fullstar = '<img src="' . $this->icon_path . 'radio-checked.png" height="16" width="16" />';
			$emptystar = '<img src="' . $this->icon_path . 'radio-unchecked.png" height="16" width="16" />';
		}

		$score = $this->settings['summary']['individual_score'] && isset( $submission_data['scoredata'] ) && !empty( $submission_data['scoredata'] ) && isset( $submission_data['scoredata']['max_score'] );
		if ( $score ) {
			$rowspan++;
		}

		$tr = false;
		?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="<?php echo $rowspan; ?>">
				<?php echo $element_data['title']; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $element_data['subtitle']; ?></span>
				<?php if ( $element_data['description'] !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $element_data['description'] ); ?>
				</div>
				<?php endif; ?>
			</th>
			<?php foreach ( $element_data['settings']['options'] as $o_key => $op ) : ?>
			<?php
			$op_icon = '<img src="' . $this->icon_path . 'thumbs-up.png" height="16" width="16" />';
			if ( isset( $submission_data['options'][$o_key] ) && $submission_data['options'][$o_key] < $element_data['settings']['max'] / 2 ) {
				$op_icon = '<img src="' . $this->icon_path . 'thumbs-up2.png" height="16" width="16" />';
			}

			?>
			<?php if ( $tr ) echo '</tr><tr style="' . $this->email_styling['tr'] . '">'; ?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $op_icon; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php echo $op; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php for ( $i = 1; $i <= $element_data['settings']['max']; $i++ ) : ?>
				<?php if ( isset( $submission_data['options'][$o_key] ) && (int) $submission_data['options'][$o_key] >= $i ) : ?>
				<?php echo $fullstar; ?>
				<?php else : ?>
				<?php echo $emptystar; ?>
				<?php endif; ?>
				<?php endfor; ?>
			</td>

			<?php $tr = true; ?>
			<?php endforeach; ?>
			<?php if ( $score ) : ?>
			</tr>
			<tr style="<?php echo $this->email_styling['tr']; ?>">
				<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
				<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
				<td style="<?php echo $this->email_styling['td']; ?>">
					<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
				</td>
			<?php endif; ?>
		<?php
	}

	public function make_slider( $title, $subtitle, $submission_data, $description = '', $prefix = '', $suffix = '', $score = false ) {
		$value = $submission_data['value'];
		$score = ( $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && ! empty( $submission_data['scoredata']['max_score'] ) );
		$img = '<img src="' . $this->icon_path . 'settings.png" height="16" width="16" />';
		$rowspan = 1;
		if ( $score ) {
			$rowspan = 2;
		}
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="<?php echo $rowspan; ?>">
				<?php echo $title; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $subtitle; ?></span>
				<?php if ( $description !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php echo $prefix . $value . $suffix; ?>
			</td>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}
	public function make_slider_inner( $title, $value, $prefix = '', $suffix = '' ) {
		$img = '<img src="' . $this->icon_path . 'settings.png" height="16" width="16" />';
?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1"><?php echo $title; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php echo $prefix . $value . $suffix; ?>
			</td>
		<?php
	}

	public function make_range( $title, $subtitle, $submission_data, $description = '', $prefix = '', $suffix = '', $score = true ) {
		$value = $submission_data['values'];
		$score = ( $this->settings['summary']['individual_score'] && $score && isset( $submission_data['scoredata'] ) && ! empty( $submission_data['scoredata']['max_score'] ) );
		$img = '<img src="' . $this->icon_path . 'settings.png" height="16" width="16" />';
		$rowspan = 1;
		if ( $score ) {
			$rowspan = 2;
		}
?>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="2" scope="row" rowspan="<?php echo $rowspan; ?>">
				<?php echo $title; ?><br /><span class="description" style="<?php echo $this->email_styling['description']; ?>"><?php echo $subtitle; ?></span>
				<?php if ( $description !== '' ) : ?>
				<div class="ipt_uif_richtext">
					<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
				</div>
				<?php endif; ?>
			</th>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="2">
				<?php printf( __( 'from %3$s%1$d%4$s to %3$s%2$d%4$s', 'ipt_fsqm' ), $value['min'], $value['max'], $prefix, $suffix ); ?>
			</td>
		<?php if ( $score ) : ?>
		</tr>
		<tr style="<?php echo $this->email_styling['tr']; ?>">
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons"><?php echo $this->score_img; ?></td>
			<th style="<?php echo $this->email_styling['th']; ?>" colspan="1"><?php echo $this->settings['summary']['score_title']; ?></th>
			<td style="<?php echo $this->email_styling['td']; ?>">
				<?php echo $submission_data['scoredata']['score']; ?> / <?php echo $submission_data['scoredata']['max_score']; ?>
			</td>
		<?php endif; ?>
		<?php
	}
	public function make_range_inner( $title, $value, $prefix = '', $suffix = '' ) {
		$img = '<img src="' . $this->icon_path . 'settings.png" height="16" width="16" />';
?>
			<td style="<?php echo $this->email_styling['icons']; ?>" class="icons">
				<?php echo $img; ?>
			</td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1"><?php echo $title; ?></td>
			<td style="<?php echo $this->email_styling['td']; ?>" colspan="1">
				<?php printf( __( 'from %3$s%1$d%4$s to %3$s%2$d%4$s', 'ipt_fsqm' ), $value['min'], $value['max'], $prefix, $suffix ); ?>
			</td>
		<?php
	}
}
