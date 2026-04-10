<h1>Nutzerverwaltung</h1>

<p><?= count($users) ?> registrierte Nutzer</p>

<div class="table-scroll">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Benutzername</th>
            <th>E-Mail</th>
            <th>Rolle</th>
            <th>Registriert</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= hsc($user['username']) ?></td>
                <td><?= hsc($user['email']) ?></td>

                <td>
                    <?php if ($user['id'] === currentUser()['id']): ?>
                        <!--
                            Die eigene Rolle wird nur als Text angezeigt.
                            Ein Dropdown würde den Selbstschutz auf UI-Ebene untergraben.
                        -->
                        <strong><?= hsc($user['role']) ?></strong>
                        <em>(eigenes Konto)</em>
                    <?php else: ?>
                        <form method="POST" action="index.php?action=admin.updateRole"
                              style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" onchange="this.form.submit()">
                                <?php foreach (['user', 'moderator', 'admin'] as $role): ?>
                                    <option value="<?= $role ?>"
                                        <?= $user['role'] === $role ? 'selected' : '' ?>>
                                        <?= hsc($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </td>

                <td><?= hsc($user['created_at']) ?></td>

                <td>
                    <?php if ($user['id'] !== currentUser()['id']): ?>
                        <form method="POST" action="index.php?action=admin.deleteUser"
                              style="display:inline"
                              onsubmit="return confirm('Nutzer &bdquo;<?= hsc($user['username']) ?>&ldquo; und ALLE zugehörigen Themen/Beiträge wirklich löschen?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="delete">Löschen</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<p><a href="index.php?action=topics.index">&larr; Zurück zur Forumsübersicht</a></p>
