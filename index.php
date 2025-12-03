<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Highloadblock\HighloadBlockTable;

Asset::getInstance()->addCss('/styles.css');
Loader::includeModule('highloadblock');

$hlblockIdCourse = 3;
$hlblock = HighloadBlockTable::getById($hlblockIdCourse)->fetch();
$entity = HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$message = '';
$editingCourse = null;

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['UF_NAME'] ?? '');

    if ($action === 'add' && $name !== '') {
        $result = $entityDataClass::add(['UF_NAME' => $name]);
        $message = $result->isSuccess() ? "Курс добавлен" : "Ошибка добавления: ".implode(', ', $result->getErrorMessages());
    } elseif ($action === 'edit' && $id > 0 && $name !== '') {
        $result = $entityDataClass::update($id, ['UF_NAME' => $name]);
        $message = $result->isSuccess() ? "Курс обновлен" : "Ошибка обновления: ".implode(', ', $result->getErrorMessages());
    } elseif ($action === 'delete' && $id > 0) {
        $result = $entityDataClass::delete($id);
        $message = $result->isSuccess() ? "Курс удален" : "Ошибка удаления: ".implode(', ', $result->getErrorMessages());
    }
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($editId > 0) {
        $res = $entityDataClass::getById($editId)->fetch();
        if ($res) {
            $editingCourse = $res;
        }
    }
}

$rsCourses = $entityDataClass::getList(['select' => ['ID', 'UF_NAME'], 'order' => ['ID' => 'ASC']]);
?>

<h1>Список курсов</h1>

<?php if ($message): ?>
    <div class="message"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<!-- Форма добавления или редактирования -->
<h2><?= $editingCourse ? 'Редактировать курс' : 'Добавить курс' ?></h2>
<form method="post" action="">
    <input type="hidden" name="action" value="<?= $editingCourse ? 'edit' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $editingCourse['ID'] ?? 0 ?>">
    <label>
        Название курса:<br>
        <input type="text" name="UF_NAME" required value="<?= htmlspecialchars($editingCourse['UF_NAME'] ?? '') ?>">
    </label><br><br>
    <button type="submit" class="button-link"><?= $editingCourse ? 'Сохранить изменения' : 'Добавить курс' ?></button>
    <?php if ($editingCourse): ?>
        <a href="index.php" class="button-link" style="margin-left:10px;">Отмена</a>
    <?php endif; ?>
</form>

<hr>

<ul class="course-list">
<?php while ($course = $rsCourses->fetch()): ?>
    <li>
        <div class="course-title">
            <a href="course_blocks.php?course_id=<?= $course['ID'] ?>">
                <?= htmlspecialchars($course['UF_NAME']) ?>
            </a>
        </div>
        <div class="course-actions">
			<a href="?edit=<?= $course['ID'] ?>" class="btn-action btn-edit">Редактировать</a>
			<form method="post" style="display: contents;" onsubmit="return confirm('Удалить курс?')">
				<input type="hidden" name="action" value="delete" />
				<input type="hidden" name="id" value="<?= $course['ID'] ?>" />
				<button type="submit" class="btn-action btn-delete">Удалить</button>
			</form>
		</div>

    </li>
<?php endwhile; ?>
</ul>


<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>
