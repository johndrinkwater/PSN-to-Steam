<?php
	require 'vendor/autoload.php';

	use Seld\JsonLint\JsonParser;

	function is_set( $variable ) {
		return isset( $variable );
	}

	function is_npcommid( $variable ) {
		return is_string( $variable );
	}

	class PSNSteamTest extends PHPUnit_Framework_TestCase {

		public function testFileExists( ) {

			// Trying to get dataProvider to work with depends in phpunit requires some serious magic
			$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'GAMES.json';

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
				'id'		=> 'is_set',
				'title'		=> 'is_string',
				'note'		=> 'is_string',
				'mapping'	=> 'is_set'
			);

			foreach( $games as $appID => $keys ) {

				$this->assertTrue( is_numeric( $appID ), 'Key "' . $appID . '" must be numeric' );

				if ( is_array( $keys ) ) {

					$this->assertNotEmpty( $keys, '"' . $appID . '" can not be an empty array' );

					foreach( $keys as $key => $value ) {

						$this->assertArrayHasKey( $key, $allowedKeys, 'Invalid key "' . $key . '" in "' . $appID . '"' );
						$this->assertTrue( $allowedKeys[ $key ]( $value ), '"' . $key . '" in "' . $appID . '" is not "' . $allowedKeys[ $key ] . '"' );

						if ( $key === 'id' ) {

							if ( is_array( $value ) ) {

								$this->assertNotEmpty( $value, '"' . $key . '" can not be an empty array' );
								foreach( $value as $npcommid ) {
									$this->assertTrue( is_npcommid( $npcommid ), $npcommid . ' field in "' . $key . '" in "' . $appID . '" must be a string' );
								}
							} else {
								$this->assertTrue( is_npcommid( $value ), $key . ' key in "' . $appID . '" must be a string' );
							}

						} else if ( $key === 'note' ) {

							$this->assertNotEmpty( $value, '"' . $key . '" in "' . $appID . '" can not be an empty string' );

						} else if ( $key === 'mapping' ) {

							// make robust test here
							if ( is_array( $value ) ) {

								$this->assertNotEmpty( $value, '"' . $key . '" in "' . $appID . '" can not be an empty array' );
								$maps = $value;
								ksort( $maps, SORT_NUMERIC );

								if ( $value !== $maps ) {
									$trophyKeys = array_keys( $value );
									$trophySortedKeys = array_keys( $maps );
									$cachedCount = count( $trophyKeys );
									unset( $maps, $value );

									for( $i = 0; $i < $cachedCount; ++$i ) {

										$message = '';
										if ( $trophyKeys[ $i ] !== $trophySortedKeys[ $i ] ) {

											$where = array_search( $trophyKeys[ $i ], $trophySortedKeys ) - array_search( $trophySortedKeys[ $i ], $trophyKeys );
											$message = $where > 0 ? $trophyKeys[ $i ] . '" is far too early' : ( $where == 0 ? $trophyKeys[ $i ] . '" is on an adjacent line' : $trophySortedKeys[ $i ] . '" is far too late' );
										}
										$this->assertEquals( $trophyKeys[ $i ], $trophySortedKeys[ $i ], 'Mapping must be sorted by trophyid, "' . $message );
									}
								}

							} else {

								// for direct mappings, we use "mapping": false
								$this->assertTrue( $value === false, '"' . $key . '" is not an array, or set to false"' );
							}
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
