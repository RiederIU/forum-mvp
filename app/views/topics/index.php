<h1>Forumsübersicht</h1>

<form method="GET" action="index.php" class="search-form">
    <input type="hidden" name="action" value="topics.index">
    <input type="text" name="search" placeholder="Themen und Beiträge durchsuchen…"
           value="<?= hsc($search) ?>">
    <button type="submit">Suchen</button>
</form>

<?php if (isLoggedIn()): ?>
    <p><a href="index.php?action=topics.create">+ Neues Thema erstellen</a></p>
<?php endif; ?>

<?php if (count($topics) === 0): ?>
    <p>Keine Themen gefunden.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Thema</th>
                <th>Autor</th>
                <th>Beiträge</th>
                <th>Erstellt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topics as $topic): ?>
                <tr>
                    <td>
                        <a href="index.php?action=topics.show&id=<?= $topic['id'] ?>">
                            <?= hsc($topic['title']) ?>
                        </a>
                    </td>
                    <td><?= hsc($topic['author']) ?></td>
                    <td><?= (int) $topic['post_count'] ?></td>
                    <td><?= hsc($topic['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    $baseUrl = 'index.php?action=topics.index';
    if ($search !== '') {
        $baseUrl .= '&search=' . urlencode($search);
    }
    require __DIR__ . '/../layout/pagination.php';
    ?>
<?php endif; ?>
