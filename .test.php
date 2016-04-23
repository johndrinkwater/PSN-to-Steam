<?php
	require 'vendor/autoload.php';

	use Seld\JsonLint\JsonParser;

	function is_set( $variable ) {
		return isset( $variable );
	}

	function is_list( $variable ) {
		if ( !is_array( $variable ) )
			return false;
		return array_keys( $variable ) === range( 0, count( $variable ) - 1 );
	}

	function is_assoc( $variable ) {
		if ( !is_array( $variable ) )
			return false;
		$keys = array_keys( $variable );
		return $keys !== array_keys( $keys );
	}

	function is_npcommid( $variable ) {

		if ( is_string( $variable ) && preg_match( '/NPWR[0-9]{5}_[0-9]{2}/', $variable ) ) {

			return true;
		}

		return false;
	}

	function is_appid( $variable ) {

		return is_integer( $variable );
	}

	class PSNSteamTest extends PHPUnit_Framework_TestCase {

		public function testFileExists( ) {

			$fileToCheck = 'GAMES.json';
			if ( isset( $_SERVER['LISTING'] ) ) {
				$fileToCheck = $_SERVER[ 'LISTING' ];
			}

			// Trying to get dataProvider to work with depends in phpunit requires some serious magic
			$filePath = __DIR__ . DIRECTORY_SEPARATOR . $fileToCheck;

			$this->assertFileExists( $filePath );

			return $filePath;
		}

		/**
		* @depends testFileExists
		*/
		public function testFileNotEmpty( $filePath )
		{
			$games = file_get_contents( $filePath );

			$this->assertNotEmpty( $games );

			return $games;
		}

		/**
		* We're sadistic bastards that only allow tabs
		*
		* @depends testFileNotEmpty
		*/
		public function testWhitespace( $games ) {

			$this->assertNotRegExp( '/^ +/m', $games, 'Spaces used, we only allow tabs' );
			$this->assertNotRegExp( '/^\t+ +/m', $games, 'Tabs mixed with spaces, we only allow tabs' );

			$games = trim( $games );

			// add a blank line test to give better errors
			$this->assertNotRegExp( '/\s$/m', $games, 'End of line whitespace found, fix it' );
			$this->assertNotRegExp( '/^$/m', $games, 'Empty line found, fix it' );

			return $games;
		}

		/**
		* @depends testWhitespace
		*/
		public function testJSON( $games ) {

			try	{
				$parser = new JsonParser();
				$games = $parser->parse( $games, JsonParser::DETECT_KEY_CONFLICTS + JsonParser::PARSE_TO_ASSOC );

			} catch ( Exception $e ) {
				$this->assertTrue( 'parsing', $e->getMessage() );
			}

			// TODO make better is_ tests for mixed fields
			$allowedKeys = Array(
				'appid'		=> 'is_set',
				'title'		=> 'is_string',
				'duplicate'	=> 'is_npcommid',
				'note'		=> 'is_string',
				'map'		=> 'is_set',
				'offset'	=> 'is_string'
			);

			foreach( $games as $appID => $keys ) {

				$this->assertTrue( is_npcommid( $appID ), 'Key "' . $appID . '" must be a Sony game ID' );

				if ( is_array( $keys ) ) {

					$this->assertNotEmpty( $keys, '"' . $appID . '" can not be an empty array' );

					foreach( $keys as $key => $value ) {

						$this->assertArrayHasKey( $key, $allowedKeys, 'Invalid key "' . $key . '" in "' . $appID . '"' );
						$this->assertTrue( $allowedKeys[ $key ]( $value ), '"' . $key . '" in "' . $appID . '" is not "' . $allowedKeys[ $key ] . '"' );

						if ( $key === 'appid' ) {

							if ( is_array( $value ) ) {

								$this->assertNotEmpty( $value, '"' . $key . '" can not be an empty array' );
								foreach( $value as $steamappid ) {
									$this->assertTrue( is_appid( $steamappid ), $steamappid . ' field in "' . $key . '" in "' . $appID . '" must be a Steam appid' );
								}
							} else {
								$this->assertTrue( is_appid( $value ), $key . ' key in "' . $appID . '" must be a Steam appid' );
							}

						} else if ( $key === 'note' ) {

							$this->assertNotEmpty( $value, '"' . $key . '" in "' . $appID . '" can not be an empty string' );

						} else if ( $key === 'map' ) {

							if ( is_string( $value ) ) {

								if ( strpos( $value, '%d' ) !== false ) {

									/* We are a direct mapping of 0 → name_0, 1 → name_1 */
								} else if ( strpos( $value, '%02d' ) !== false ) {

									/* We are a direct mapping of 0 → name_00, 1 → name_01 */
								} else {

									$this->assertTrue( false, 'Value "' . $value . '" for "map" is not a valid map string' );
								}

							} else if ( is_bool( $value ) ) {

								// for direct mappings, we use "map": false
								// XXX rework this to confirm NO map exists
								$this->assertTrue( $value === false, '"' . $key . '" is not a valid value' );

							} else if ( is_list( $value ) ) {

								$this->assertNotEmpty( $value, '"' . $key . '" in "' . $appID . '" can not be an empty array' );

								// XXX: Found the first title that mapped Platinum to a Steam achievement, Trine!
								// $this->assertFalse( array_key_exists( "0", $value ), '"' . $key . '" in "' . $appID . '" has an achievement mapped to Platinum trophy' );

								// Discover is we are a multi-grouped map
								foreach( $value as $index => $group ){

									if ( is_string( $group ) ) {

									} else if ( is_assoc( $group ) ) {

										// Our current process makes this recursive for an extra level…
										// XXX break out duplicate test

									} else if ( is_list( $group ) ) {

										// Find accidental duplicates, remove "-1" entries and flatten multiple pairings
										$valuesmapped = array_values( $group );
										foreach( $valuesmapped as $index => $achievement ){

											if ( $achievement == "-1" ) {
												unset( $valuesmapped[ $index ] );
											}
											if ( is_array( $achievement ) ) {
												$valuesmapped[ $index ] = implode( "-", $achievement );
											}
										}
										$this->assertTrue( count( $valuesmapped ) === count( array_unique( $valuesmapped ) ), '"' . $key . '" in "' . $appID . '" has a duplicate mapping' );
										unset( $valuesmapped );
									}
								}

							} else {

								// XXX We should never get here
							}
						} else if ( $key === 'offset' ) {

							// XXX verify "map" is set, and is using "%d" format
							// XXX validate info inside to support:
							// -count : basically to mask the current count (starts with 0)
							// [+-][0-9]* : the offset to the passed in count
						}
					}
				} else {
					$this->assertTrue( false, 'Key "' . $appID . '" has an invalid value' );
				}
			}

			return $games;
		}

		/**
		* @depends testJSON
		*/
		public function testSorting( $games ) {

			$gamesSorted = $games;

			ksort( $gamesSorted );

			if ( $games !== $gamesSorted ) {

				$gamesKeys = array_keys( $games );
				$gamesSortedKeys = array_keys( $gamesSorted );
				$cachedCount = count( $gamesKeys );

				unset( $games, $gamesSorted );

				for( $i = 0; $i < $cachedCount; ++$i ) {

					$message = '';
					if ( $gamesKeys[ $i ] !== $gamesSortedKeys[ $i ] ) {

						$where = array_search( $gamesKeys[ $i ], $gamesSortedKeys ) - array_search( $gamesSortedKeys[ $i ], $gamesKeys );
						$message = $where > 0 ? $gamesKeys[ $i ] . '" is far too early' : ( $where == 0 ? $gamesKeys[ $i ] . '" is on an adjacent line' : $gamesSortedKeys[ $i ] . '" is far too late' );
					}
					$this->assertEquals( $gamesKeys[ $i ], $gamesSortedKeys[ $i ], 'File must be sorted correctly by appid, "' . $message );
				}
			}
		}
	}
