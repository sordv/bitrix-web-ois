<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Highloadblock\HighloadBlockTable;

Asset::getInstance()->addCss('/styles.css');

Loader::includeModule('highloadblock');

$blockId = intval($_GET['block_id'] ?? 0);
if ($blockId <= 0) {
    echo "Ошибка: Не указан ID блока.";
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
    exit;
}

// Получаем название блока
$hlblockIdBlocks = 2;
$hlblockBlock = HighloadBlockTable::getById($hlblockIdBlocks)->fetch();
$entityBlock = HighloadBlockTable::compileEntity($hlblockBlock);
$entityDataClassBlock = $entityBlock->getDataClass();

$blockName = '';
$courseIdForBack = 0;
$blockRes = $entityDataClassBlock::getList(['filter' => ['ID' => $blockId],'limit'=>1]);
if ($block = $blockRes->fetch()) {
    $blockName = $block['UF_NAME'];
    $courseIdForBack = $block['UF_COURSE_ID'];
}

$hlblockIdAnswers = 1;
$hlblockAnswers = HighloadBlockTable::getById($hlblockIdAnswers)->fetch();
$entityAnswers = HighloadBlockTable::compileEntity($hlblockAnswers);
$entityDataClassAnswers = $entityAnswers->getDataClass();

$message = '';
$editingAnswer = null;

// Обработка POST формы редактирования ответов 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'UF_SCORE' => intval($_POST['UF_SCORE'] ?? 0),
        'UF_REVIEW' => trim($_POST['UF_REVIEW'] ?? ''),
    ];
    if ($id > 0) {
        $result = $entityDataClassAnswers::update($id, $data);
        $message = $result->isSuccess() ? 'Ответ обновлен' : 'Ошибка: ' . implode(', ', $result->getErrorMessages());
    }
}

// Если есть параметр edit — загружаем ответ для редактирования
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($editId > 0) {
        $editingAnswer = $entityDataClassAnswers::getById($editId)->fetch();
    }
}

// получаем ответы для текущего блока
$rsAnswers = $entityDataClassAnswers::getList([
    'filter' => ['UF_BLOCK_ID' => $blockId],
    'order' => ['ID' => 'ASC']
]);
?>

<h1>Ответы на блок: <?=htmlspecialchars($blockName)?></h1>

<?php if ($message): ?>
<div class="message"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<a href="course_blocks.php?course_id=<?=$courseIdForBack?>" class="button-link">← Вернуться к блокам</a>

<?php if ($editingAnswer): ?>
<h2>Редактировать ответ ID <?= $editingAnswer['ID'] ?></h2>
<form method="post" action="">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="id" value="<?= $editingAnswer['ID'] ?>" />
    <label>Баллы: <input type="number" name="UF_SCORE" value="<?=intval($editingAnswer['UF_SCORE'])?>" required></label><br>
    <label>Отзыв:<br><textarea name="UF_REVIEW"><?=htmlspecialchars($editingAnswer['UF_REVIEW'])?></textarea></label><br>
    <button type="submit" class="button-link">Сохранить</button>
    <a href="block_answers.php?block_id=<?= $blockId ?>" class="button-link" style="margin-left:10px;">Отмена</a>
</form>
<hr>
<?php endif; ?>

<table class="answers-table">
<thead>
    <tr>
        <th>Студент ID</th>
        <th>Баллы</th>
        <th>Отзыв</th>
        <th>Файл</th>
        <th>Действия</th>
    </tr>
</thead>
<tbody>
<?php if ($rsAnswers->getSelectedRowsCount() === 0): ?>
<tr>
    <td colspan="5" style="text-align: center; padding: 20px;">Нет ответов</td>
</tr>
<?php else: ?>
<?php while ($answer = $rsAnswers->fetch()): ?>
<tr>
    <td><?=htmlspecialchars($answer['UF_STUDENT_ID'])?></td>
    <td><?=intval($answer['UF_SCORE'])?></td>
    <td><?=nl2br(htmlspecialchars($answer['UF_REVIEW']))?></td>
    <td>
    <?php if (!empty($answer['UF_FILE'])): ?>
        <a href="<?=htmlspecialchars($answer['UF_FILE'])?>" download class="download-link">Скачать</a>
    <?php endif; ?>
    </td>
    <td>
        <a href="?block_id=<?= $blockId ?>&edit=<?= $answer['ID'] ?>" class="button-link small">Редактировать</a>
    </td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>
</table>

<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>
