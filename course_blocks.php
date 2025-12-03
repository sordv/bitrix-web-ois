<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Highloadblock\HighloadBlockTable;

Asset::getInstance()->addCss('/styles.css');
Loader::includeModule('highloadblock');
Loader::includeModule('main');

$courseId = intval($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    echo "Ошибка: Не указан ID курса.";
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
    exit;
}

function formatDateForBitrix($date) {
    if (empty($date)) return null;
    $dateTime = new DateTime($date);
    return $dateTime->format('d.m.Y 00:00:00');
}

function formatDateForInput($bitrixDate) {
    if (empty($bitrixDate)) return '';
    $dateTime = new DateTime($bitrixDate);
    return $dateTime->format('Y-m-d');
}

// Курс
$hlblockIdCourse = 3;
$hlblockCourse = HighloadBlockTable::getById($hlblockIdCourse)->fetch();
$entityCourse = HighloadBlockTable::compileEntity($hlblockCourse);
$entityDataClassCourse = $entityCourse->getDataClass();

$courseName = '';
$courseRes = $entityDataClassCourse::getList(['filter' => ['ID' => $courseId],'limit'=>1]);
if ($course = $courseRes->fetch()) {
    $courseName = $course['UF_NAME'];
}

// Блоки
$hlblockIdBlocks = 2;
$hlblockBlocks = HighloadBlockTable::getById($hlblockIdBlocks)->fetch();
$entityBlocks = HighloadBlockTable::compileEntity($hlblockBlocks);
$entityDataClassBlocks = $entityBlocks->getDataClass();

$message = '';
$editingBlock = null;

// POST обработка
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    
    $data = [
        'UF_COURSE_ID' => (string)$courseId,
        'UF_SORT_ORDER' => (string)($_POST['UF_SORT_ORDER'] ?? '0'),
        'UF_NAME' => trim($_POST['UF_NAME'] ?? ''),
        'UF_DESCRIPTION' => trim($_POST['UF_DESCRIPTION'] ?? ''),
        'UF_TYPE' => trim($_POST['UF_TYPE'] ?? ''),
        'UF_MAXSCORE' => intval($_POST['UF_MAXSCORE'] ?? 0)
    ];

    if (!empty($_POST['UF_DATASTART'])) {
        $data['UF_DATASTART'] = formatDateForBitrix($_POST['UF_DATASTART']);
    }
    if (!empty($_POST['UF_DATAEND'])) {
        $data['UF_DATAEND'] = formatDateForBitrix($_POST['UF_DATAEND']);
    }

    if (!empty($_FILES['UF_FILE']['tmp_name']) && $_FILES['UF_FILE']['error'] === UPLOAD_ERR_OK) {
        $fileArray = CFile::MakeFileArray($_FILES['UF_FILE']);
        if ($fileArray) {
            $data['UF_FILE'] = $fileArray;
        }
    }

    if ($action === 'add') {
        $result = $entityDataClassBlocks::add($data);
        $message = $result->isSuccess() ? "Блок добавлен ID=".$result->getId() : "Ошибка: ".implode(', ', $result->getErrorMessages());
    } elseif ($action === 'edit' && $id > 0) {
        $result = $entityDataClassBlocks::update($id, $data);
        $message = $result->isSuccess() ? "Блок обновлен" : "Ошибка: ".implode(', ', $result->getErrorMessages());
    } elseif ($action === 'delete' && $id > 0) {
        $blockData = $entityDataClassBlocks::getById($id)->fetch();
        if ($blockData && !empty($blockData['UF_FILE'])) {
            CFile::Delete($blockData['UF_FILE']);
        }
        $result = $entityDataClassBlocks::delete($id);
        $message = $result->isSuccess() ? "Блок удален" : "Ошибка удаления";
    }
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($editId > 0) {
        $editingBlock = $entityDataClassBlocks::getById($editId)->fetch();
    }
}

$rsBlocks = $entityDataClassBlocks::getList([
    'filter' => ['UF_COURSE_ID' => (string)$courseId],
    'select' => ['*'], 
    'order' => ['UF_SORT_ORDER' => 'ASC']
]);
?>

<h1>Блоки курса: <?=htmlspecialchars($courseName)?></h1>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<a href="index.php" class="button-link">← Курсы</a>

<h2><?= $editingBlock ? 'Редактирование' : 'Добавить блок' ?></h2>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $editingBlock ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editingBlock['ID'] ?? 0 ?>">

    <label>Порядок:
        <input type="text" name="UF_SORT_ORDER" value="<?= htmlspecialchars($editingBlock['UF_SORT_ORDER'] ?? '0') ?>">
    </label><br><br>

    <label>Название:
        <input type="text" name="UF_NAME" required value="<?= htmlspecialchars($editingBlock['UF_NAME'] ?? '') ?>">
    </label><br><br>

    <label>Описание:<br>
        <textarea name="UF_DESCRIPTION"><?= htmlspecialchars($editingBlock['UF_DESCRIPTION'] ?? '') ?></textarea>
    </label><br><br>

    <label>Тип:
        <input type="text" name="UF_TYPE" value="<?= htmlspecialchars($editingBlock['UF_TYPE'] ?? '') ?>">
    </label><br><br>

    <label>Дата начала:
        <input type="date" name="UF_DATASTART" value="<?= htmlspecialchars(formatDateForInput($editingBlock['UF_DATASTART'] ?? '')) ?>">
    </label><br><br>

    <label>Дата окончания:
        <input type="date" name="UF_DATAEND" value="<?= htmlspecialchars(formatDateForInput($editingBlock['UF_DATAEND'] ?? '')) ?>">
    </label><br><br>

    <label>Максимальный балл:
        <input type="number" name="UF_MAXSCORE" value="<?= intval($editingBlock['UF_MAXSCORE'] ?? 0) ?>">
    </label><br><br>

    <label>Файл (.pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg,.gif):<br>
        <input type="file" name="UF_FILE" accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg,.gif">
        <?php if ($editingBlock && !empty($editingBlock['UF_FILE'])): 
            $currentFile = CFile::GetFileArray($editingBlock['UF_FILE']);
            if ($currentFile): ?>
                <br><small>Текущий: <a href="<?=htmlspecialchars($currentFile['SRC'])?>" class="download-link" target="_blank"><?=htmlspecialchars($currentFile['ORIGINAL_NAME'])?> (<?=round($currentFile['FILE_SIZE']/1024,1)?>КБ)</a></small>
            <?php endif;
        endif; ?>
    </label><br><br>

    <button type="submit" class="button-link"><?= $editingBlock ? 'Сохранить' : 'Добавить' ?></button>
    <?php if ($editingBlock): ?>
        <a href="course_blocks.php?course_id=<?= $courseId ?>" class="button-link" style="margin-left: 10px;">Отмена</a>
    <?php endif; ?>
</form>

<hr>

<table class="blocks-table">
<thead>
<tr>
    <th>Порядок</th><th>Название</th><th>Описание</th><th>Тип</th><th>Дата начала</th>
    <th>Дата окончания</th><th>Макс. балл</th><th>Файл</th><th>Действия</th>
</tr>
</thead>
<tbody>
<?php while ($block = $rsBlocks->fetch()): ?>
<tr>
    <td><?=htmlspecialchars($block['UF_SORT_ORDER'])?></td>
    <td><a href="block_answers.php?block_id=<?=$block['ID']?>"><?=htmlspecialchars($block['UF_NAME'])?></a></td>
    <td><?=htmlspecialchars($block['UF_DESCRIPTION'])?></td>
    <td><?=htmlspecialchars($block['UF_TYPE'])?></td>
    <td><?=htmlspecialchars($block['UF_DATASTART'])?></td>
    <td><?=htmlspecialchars($block['UF_DATAEND'])?></td>
    <td><?=intval($block['UF_MAXSCORE'])?></td>
    <td>
        <?php 
        $filePath = '';
        $fileName = '';
        $fileSize = 0;
        if (!empty($block['UF_FILE'])) {
            $fileArray = CFile::GetFileArray($block['UF_FILE']);
            if ($fileArray && $fileArray['SRC']) {
                $filePath = $fileArray['SRC'];
                $fileName = $fileArray['ORIGINAL_NAME'];
                $fileSize = $fileArray['FILE_SIZE'];
            }
        }
        ?>
        <?php if ($filePath): ?>
            <a href="<?=htmlspecialchars($filePath)?>" class="download-link" target="_blank"><?=htmlspecialchars($fileName)?> (<?=round($fileSize/1024,1)?>КБ)</a>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
    <td>
    <div class="block-actions">
        <a href="?course_id=<?= $courseId ?>&edit=<?= $block['ID'] ?>" class="btn-action btn-edit">Ред.</a>
        <form method="post" action="" style="display: contents;" onsubmit="return confirm('Удалить блок?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $block['ID'] ?>">
            <button type="submit" class="btn-action btn-delete">Удал.</button>
        </form>
    </div>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
