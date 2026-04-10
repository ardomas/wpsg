<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render Program Form
 * Internal WPSG block (admin only)
 */

$action = esc_url( admin_url( 'admin-post.php' ) );
?>

<form method="post" action="<?php echo $action; ?>" class="wpsg-program-form">

    <?php wp_nonce_field( 'wpsg_save_program', 'wpsg_nonce' ); ?>

    <input type="hidden" name="action" value="wpsg_save_program">
    <input type="hidden" name="module" value="program">

    <table class="form-table">
        <tbody>

            <tr>
                <th scope="row">
                    <label for="program_title">Program Title</label>
                </th>
                <td>
                    <input
                        type="text"
                        id="program_title"
                        name="program[title]"
                        class="regular-text"
                        required
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="program_description">Description</label>
                </th>
                <td>
                    <textarea
                        id="program_description"
                        name="program[description]"
                        rows="5"
                        class="large-text"
                    ></textarea>
                </td>
            </tr>

        </tbody>
    </table>

    <?php submit_button( 'Save Program' ); ?>

</form>
