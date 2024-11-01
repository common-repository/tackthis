<div style="margin-right:20px;">
    <h2>TackThis Shortcodes</h2>
    <p>These shortcode are available for you to include in WP post.</p>
</div>

<?php
$options = get_site_option(WP_TACKTHIS_OPTIONS_NAME);
?>

<?php if ($options['shopId'] && is_numeric($options['shopId'])) { ?>
    <div>
        Your Shop Id is <code><?php echo $options['shopId']; ?></code>
    </div>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><label for="blogname">Tackthis Shop</label></th>
                <td>
                    <code>[tack]api=<?php echo $options['shopId']; ?>x[this]</code>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="blogname">Tackthis Category</label></th>
                <td>
                    <code>[tack]api=<?php echo $options['shopId']; ?>&cid=#####[this]</code>
                    <span>
                        Replace "#####" with category ID.
                    </span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="blogname">Tackthis Product</label></th>
                <td>
                    <code>[tack]api=<?php echo $options['shopId']; ?>&pid=#####[this]</code>
                    <span>
                        Replace "#####" with product ID.
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
<?php } else { ?>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <td>
                    You have not setup your TackThis Shop Settings. Please complete the setup <a href="admin.php?page=wptackthis_dashboard">here</a>.
                </td>
            </tr>
        </tbody>
    </table>
<?php } ?>
