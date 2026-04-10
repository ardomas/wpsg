<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Domain constants for Person Records
 *
 * This class defines all valid record_type and subtype
 * used across WPSG.
 */
final class WPSG_PersonRecordTypes {

    /* ---------------------------------------------------------
     * MAIN RECORD TYPES
     * --------------------------------------------------------- */

    // Student-related
    public const STUDENT_ACTIVITY   = 'student_activity';
    public const STUDENT_EVALUATION = 'student_evaluation';

    // Parent-related
    public const PARENT_HISTORY     = 'parent_history';

    // Staff-related
    public const STAFF_ASSIGNMENT   = 'staff_assignment';
    public const STAFF_ROLE_HISTORY = 'staff_role_history';

    // Teacher-related
    public const TEACHING_SCHEDULE  = 'teaching_schedule';

    // Generic / shared
    public const GENERAL_NOTE       = 'general_note';

    /* ---------------------------------------------------------
     * COMMON SUBTYPES
     * --------------------------------------------------------- */

    public const SUBTYPE_DAILY   = 'daily';
    public const SUBTYPE_WEEKLY  = 'weekly';
    public const SUBTYPE_MONTHLY = 'monthly';
    public const SUBTYPE_YEARLY  = 'yearly';

    public const SUBTYPE_FORMAL  = 'formal';
    public const SUBTYPE_INFORMAL= 'informal';

    /* ---------------------------------------------------------
     * VALIDATION HELPERS
     * --------------------------------------------------------- */

    /**
     * Get all record types
     */
    public static function all_types() : array {
        return [
            self::STUDENT_ACTIVITY,
            self::STUDENT_EVALUATION,
            self::PARENT_HISTORY,
            self::STAFF_ASSIGNMENT,
            self::STAFF_ROLE_HISTORY,
            self::TEACHING_SCHEDULE,
            self::GENERAL_NOTE,
        ];
    }

    /**
     * Get all common subtypes
     */
    public static function all_subtypes() : array {
        return [
            self::SUBTYPE_DAILY,
            self::SUBTYPE_WEEKLY,
            self::SUBTYPE_MONTHLY,
            self::SUBTYPE_YEARLY,
            self::SUBTYPE_FORMAL,
            self::SUBTYPE_INFORMAL,
        ];
    }

    /**
     * Validate record type
     */
    public static function is_valid_type( string $type ) : bool {
        return in_array( $type, self::all_types(), true );
    }

    /**
     * Validate subtype
     * (nullable allowed)
     */
    public static function is_valid_subtype( ?string $subtype ) : bool {
        if ( $subtype === null ) {
            return true;
        }

        return in_array( $subtype, self::all_subtypes(), true );
    }
}
