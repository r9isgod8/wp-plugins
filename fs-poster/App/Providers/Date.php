<?php

namespace FSPoster\App\Providers;

class Date
{

	private static $time_zone;


	public static function getTimeZone()
	{
		if( is_null( self::$time_zone ) )
		{

			$tz_string = get_option( 'timezone_string' );
			$tz_offset = get_option( 'gmt_offset', 0 );

			if ( !empty( $tz_string ) )
			{
				$timezone = $tz_string;
			}
			else if ( !empty( $tz_offset ) )
			{
				$hours = abs( (int)$tz_offset );
				$minutes = ( abs($tz_offset) - $hours ) * 60;

				$timezone = ($tz_offset > 0 ? '+' : '-') . sprintf('%02d:%02d', $hours, $minutes);
			}
			else
			{
				$timezone = 'UTC';
			}

			self::$time_zone = new \DateTimeZone( $timezone );
		}

		return self::$time_zone;
	}

	public static function dateTime( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDateTime() );
	}

	public static function datee( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDate() );
	}

	public static function time( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatTime() );
	}


	public static function dateTimeSQL( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDateTime( true ) );
	}

	public static function dateSQL( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatDate( true ) );
	}

	public static function format( $format , $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( $format );
	}

	public static function timeSQL( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( self::formatTime( true ) );
	}


	public static function epoch( $date = 'now', $modify = false )
	{
		$datetime = new \DateTime( $date, self::getTimeZone() );

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->getTimestamp();
	}

	public static function formatDate( $forSQL = false )
	{
		if( $forSQL )
		{
			return 'Y-m-d';
		}
		else
		{
			return Helper::getOption('date_format', 'Y-m-d');
		}
	}

	public static function formatTime( $forSQL = false )
	{
		if( $forSQL )
		{
			return 'H:i:s';
		}
		else
		{
			return Helper::getOption('time_format', 'H:i');
		}
	}

	public static function formatDateTime( $forSQL = false )
	{
		return self::formatDate( $forSQL ) . ' ' . self::formatTime( $forSQL );
	}


	public static function UTCDateTime( $date )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		$datetime->setTimezone( new \DateTimeZone('UTC') );

		return $datetime->format( 'Y-m-d\TH:i:sP' );
	}

	public static function year( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( 'Y' );
	}

	public static function month( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( 'm' );
	}

	public static function day( $date = 'now', $modify = false )
	{
		if( is_numeric( $date ) )
		{
			$datetime = new \DateTime( 'now', self::getTimeZone() );
			$datetime->setTimestamp( $date );
		}
		else
		{
			$datetime = new \DateTime( $date, self::getTimeZone() );
		}

		if( !empty( $modify ) )
		{
			$datetime->modify( $modify );
		}

		return $datetime->format( 'd' );
	}

	public static function lastDateOfMonth( $year, $month )
	{
		$datetime = new \DateTime( "{$year}-{$month}-01", self::getTimeZone() );

		return $datetime->format( 'Y-m-t' );
	}

}
