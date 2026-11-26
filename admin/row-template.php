<tr class="text-center">

    <td>
        <form method="post" class="d-flex">
            <input type="text" name="new_name" class="form-control" value="<?= htmlspecialchars($dev_name) ?>">
            <input type="hidden" name="developer_id" value="<?= $current_dev ?>">
            <button name="update_name" class="btn btn-success btn-sm ms-2">Save</button>
        </form>
    </td>

    <td>
        <table class="table table-sm table-bordered bg-light mb-0">
            <?php if (!empty($props)): ?>
                <?php foreach ($props as $p): ?>
                    <tr>
                        <td class="text-start"><?= htmlspecialchars($p) ?></td>
                        <td style="width:50px;">
                            <form method="post">
                                <input type="hidden" name="remove_property" value="<?= htmlspecialchars($p) ?>">
                                <input type="hidden" name="developer_id" value="<?= $current_dev ?>">
                                <button class="btn btn-danger btn-sm">X</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No Properties</td>
                </tr>
            <?php endif; ?>
        </table>
    </td>

    <td>
        <form method="post" onsubmit="return confirm('Delete developer with all properties?')">
            <input type="hidden" name="delete_id" value="<?= $current_dev ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
        </form>
    </td>

</tr>
