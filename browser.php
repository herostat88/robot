<?php

	class Browser
		{
			# Robot itself
			public $web;

			# Setting for browser - local cache, etc...
			protected $settings = array
				(
					'timeout'						=> 120,
					'delay'							=> 0,
					'debug'							=> FALSE,
					'error_tag'						=> NULL,
					'user_agent_mobile'			=> 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
					'user_agent_desktop'			=> 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
					'local_cache_dir'				=> './cache',
					'cookie_jar'					=> '/tmp/cookie',

					// Default browser headers, emulate Chrome
					'headers'						=> array (
						'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
						'Accept-Language: en-US,en;q=0.8',
						'Cache-Control: no-cache',
						'Pragma: no-cache',
						'Upgrade-Insecure-Requests: 1'
					),
				);

			# Reset headers
			protected $default_headers = array
				(
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'Accept-Language: en-US,en;q=0.8',
					'Cache-Control: no-cache',
					'Pragma: no-cache',
					'Upgrade-Insecure-Requests: 1'
				);
		
			public function __construct( $error_tag = NULL, $debug = FALSE )
				{
					$this->settings['debug'] = $debug;

					if( !is_null( $error_tag ) )
						$this->settings['error_tag'] = $error_tag;

					$settings = $this->settings;

					debug( 'Start Crawler initialization...');
					#array_push( $settings['headers'], $settings['host'] );

					// Init Crawler with default settings
					$this->web = curl_init();
					curl_setopt( $this->web, CURLOPT_NOBODY,				FALSE				); // get body request
					curl_setopt( $this->web, CURLOPT_RETURNTRANSFER,	TRUE				);
					curl_setopt( $this->web, CURLOPT_USERAGENT,			$settings['user_agent_desktop']				);
					curl_setopt( $this->web, CURLOPT_HTTPHEADER,			$settings['headers']			);
					curl_setopt( $this->web, CURLOPT_AUTOREFERER,		TRUE				); // add REFERER header
					curl_setopt( $this->web, CURLOPT_FOLLOWLOCATION,	TRUE				); // add auto redirect
					curl_setopt( $this->web, CURLOPT_CONNECTTIMEOUT,	$settings['timeout']			); // set connection timeout
					curl_setopt( $this->web, CURLOPT_TIMEOUT,				$settings['timeout']			);
					curl_setopt( $this->web, CURLOPT_COOKIEJAR,			$settings['cookie_jar']		);
					// Debug options
					curl_setopt( $this->web, CURLOPT_HEADER,				$settings['debug']			); // no need headers
					curl_setopt( $this->web, CURLOPT_VERBOSE,				$settings['debug']			); // verbose output

					debug( 'Crawler initialization completed.');
				}

			public function __destruct()
				{
					$this->childObject = null;
					unset( $this->web );
				}

			public function getUrlContent( $prefix = '', $url, $cache = FALSE )
				{
					$settings = $this->settings;
					$data = FALSE;

					$localfile = $settings['local_cache_dir'] . '/' . basename( $url );
					if( !empty( $prefix ) )
						$localfile = $settings['local_cache_dir'] . '/' . $prefix . '-' . basename( $url );

					// Check for local cache first
					if( file_exists( $localfile ) && ( filesize( $localfile ) != 0 ) && $cache )
						{
							debug( 'Getting page from local cache ' . $localfile );
							return file_get_contents( $localfile );
						}
					else
						{
							debug( 'Getting remote page ' . $url . ' to ' . $localfile );

							if( $settings['delay'] > 0 )
								sleep( $settings['delay'] );

							curl_setopt( $this->web, CURLOPT_URL, $url ); // API URL
							$data = curl_exec( $this->web );
							#while( $redirectURL = curl_getinfo( $this->web, CURLINFO_EFFECTIVE_URL ) ){
							#	curl_setopt( $this->web, CURLOPT_URL, $redirectURL ); // API URL
							#	$data = curl_exec( $this->web );
							#}

							if( $settings['error_tag'] != '' ) {
								if( stripos( $data, $target['error_page'] ) !== FALSE )
									{
										debug( '[-] We got ERROR PAGE(crawler detected)' );
										return FALSE;
									}
							}

							file_put_contents( $localfile, $data );

						}

					return $data;
				}

			public function sendPostData( $url, &$data, $cache = FALSE )
				{
					if( $cache ){
						debug( '[i] Posting data disabled because CACHE enabled' );
						return TRUE;
					}

					debug( '[i] Posting data to remote website' );

					curl_setopt( $this->web, CURLOPT_POST, count( $data ) );
					curl_setopt( $this->web, CURLOPT_POSTFIELDS, http_build_query( $data ) );
					curl_setopt( $this->web, CURLOPT_URL, $url ); // API URL
					$result = curl_exec( $this->web );
					curl_setopt( $this->web, CURLOPT_POST, FALSE );

					if( isset( $result ) && !empty( $result ) )
						return $result;

					return FALSE;
				}

			public function changeHeaders()
				{
					debug( '[+] Browser headers was changed' );
					curl_setopt( $this->web, CURLOPT_HTTPHEADER, $this->settings['headers'] );
				}

			public function addHeader( $row ){
				$this->settings['headers'][] = $row;
				$this->changeHeaders();
			}

			public function resetHeaders(){
				$this->settings['headers'] = $this->default_headers;
				$this->changeHeaders();
			}

			public function setOpt( $opt, &$val )
				{
					debug( 'Set crawler option ' . $opt . ' > ' . $val );
					curl_setopt( $this->web, constant( $opt ), $val ); // Filename to save image
				}
		}

?>
