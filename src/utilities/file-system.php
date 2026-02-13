<?php
/**
 * File System Utility
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File_System {

	public static function get_upload_dir(): string {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['basedir'] ) . 'notification-hub/';
	}

	public static function ensure_upload_dir(): bool {
		$dir = self::get_upload_dir();

		if ( ! file_exists( $dir ) ) {
			return wp_mkdir_p( $dir );
		}

		return true;
	}

	public static function write_file( string $filename, string $content ): bool {
		self::ensure_upload_dir();

		$file_path = self::get_upload_dir() . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return file_put_contents( $file_path, $content ) !== false;
	}

	public static function read_file( string $filename ): string {
		$file_path = self::get_upload_dir() . $filename;

		if ( ! file_exists( $file_path ) ) {
			return '';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return file_get_contents( $file_path );
	}

	public static function delete_file( string $filename ): bool {
		$file_path = self::get_upload_dir() . $filename;

		if ( file_exists( $file_path ) ) {
			return wp_delete_file( $file_path );
		}

		return false;
	}
}
