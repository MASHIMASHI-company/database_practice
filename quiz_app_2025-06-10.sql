DROP DATABASE IF EXISTS quiz_app;
CREATE DATABASE quiz_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE quiz_app;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_admin BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  answer_index INT NOT NULL,
  tag VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE choices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  choice_text VARCHAR(255) NOT NULL,
  index_number INT NOT NULL,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  choice_id INT NOT NULL,
  answered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (choice_id) REFERENCES choices(id) ON DELETE CASCADE
);

INSERT INTO quizzes (title, content, answer_index, tag) VALUES
('HTML 基本1', 'HTMLの正式名称は何ですか？', 0, 'HTML'),
('HTML 基本2', '<a> タグの href 属性の用途は？', 1, 'HTML'),
('HTML 基本3', 'HTMLで段落を作るタグは？', 2, 'HTML'),
('HTML 基本4', '画像を表示するタグは？', 1, 'HTML'),
('HTML 基本5', 'HTMLのコメント記法は？', 2, 'HTML'),
('HTML 基本6', 'フォーム送信に使うタグは？', 3, 'HTML'),
('HTML 基本7', '見出しタグ <h1> の意味は？', 0, 'HTML'),
('HTML 基本8', '<ul> タグは何のため？', 1, 'HTML'),
('HTML 基本9', 'input タグの type="password" は？', 2, 'HTML'),
('HTML 基本10', 'HTMLで改行するには？', 3, 'HTML');

INSERT INTO choices (quiz_id, choice_text, index_number) VALUES
(1, 'HyperText Markup Language', 0),
(1, 'HighText Machine Language', 1),
(1, 'Hyper Transfer Markup Level', 2),
(1, 'HyperText Making Language', 3),

(2, 'リンク先の色を変える', 0),
(2, 'リンク先のURLを指定する', 1),
(2, '文字を太くする', 2),
(2, '画像を挿入する', 3),

(3, '<br>', 0),
(3, '<head>', 1),
(3, '<p>', 2),
(3, '<div>', 3),

(4, '<div>', 0),
(4, '<img>', 1),
(4, '<picture>', 2),
(4, '<image>', 3),

(5, '// コメント', 0),
(5, '/* コメント */', 1),
(5, '<!-- コメント -->', 2),
(5, '# コメント', 3),

(6, '<span>', 0),
(6, '<table>', 1),
(6, '<input>', 2),
(6, '<form>', 3),

(7, '最上位の見出し', 0),
(7, '小さな文字', 1),
(7, '段落', 2),
(7, 'リンク', 3),

(8, '表を作る', 0),
(8, '順不同リスト', 1),
(8, 'リンクを作る', 2),
(8, '文字を中央揃え', 3),

(9, '数値入力', 0),
(9, 'テキスト表示', 1),
(9, 'パスワード入力', 2),
(9, 'ファイルアップロード', 3),

(10, '<p>', 0),
(10, '<div>', 1),
(10, '<hr>', 2),
(10, '<br>', 3);

INSERT INTO quizzes (title, content, answer_index, tag) VALUES
('CSS 基本1', 'CSSの役割は何ですか？', 0, 'CSS'),
('CSS 基本2', 'CSSで文字の色を変更するプロパティは？', 1, 'CSS'),
('CSS 基本3', 'クラスセレクタを指定する記号は？', 2, 'CSS'),
('CSS 基本4', 'IDセレクタを指定する記号は？', 3, 'CSS'),
('CSS 基本5', '外部CSSファイルをHTMLに読み込む方法は？', 0, 'CSS'),
('CSS 基本6', 'ボックスモデルに含まれないものは？', 2, 'CSS'),
('CSS 基本7', '全てのHTML要素にスタイルを適用するセレクタは？', 1, 'CSS'),
('CSS 基本8', '要素を中央揃えするために使うプロパティは？', 3, 'CSS'),
('CSS 基本9', 'CSSで要素を隠すプロパティは？', 1, 'CSS'),
('CSS 基本10', 'メディアクエリの目的は？', 0, 'CSS');

INSERT INTO choices (quiz_id, choice_text, index_number) VALUES
(11, 'Webページの見た目を整える', 0),
(11, 'データベースを操作する', 1),
(11, 'サーバーと通信する', 2),
(11, 'JavaScriptコードを書く', 3),

(12, 'font-family', 0),
(12, 'color', 1),
(12, 'background-color', 2),
(12, 'text-style', 3),

(13, '#', 0),
(13, '/', 1),
(13, '.', 2),
(13, '*', 3),

(14, '*', 0),
(14, '.', 1),
(14, '&', 2),
(14, '#', 3),

(15, '<link rel="stylesheet" href="style.css">', 0),
(15, '<style src="style.css">', 1),
(15, '<script href="style.css">', 2),
(15, '<css link="style.css">', 3),

(16, 'padding', 0),
(16, 'margin', 1),
(16, 'border-radius', 2),
(16, 'border', 3),

(17, '#', 0),
(17, '*', 1),
(17, '.', 2),
(17, ':', 3),

(18, 'font-align', 0),
(18, 'display', 1),
(18, 'color', 2),
(18, 'text-align', 3),

(19, 'visibility: visible;', 0),
(19, 'display: none;', 1),
(19, 'text-decoration: none;', 2),
(19, 'opacity: 2;', 3),

(20, '画面サイズに応じてスタイルを変える', 0),
(20, '画像を圧縮する', 1),
(20, 'JavaScriptを実行する', 2),
(20, 'HTMLを生成する', 3);

INSERT INTO quizzes (title, content, answer_index, tag) VALUES
('JavaScript 基本1', 'JavaScriptはどのタイミングで動作しますか？', 1, 'JavaScript'),
('JavaScript 基本2', '変数を宣言する方法は？', 0, 'JavaScript'),
('JavaScript 基本3', '配列の最初の要素を取り出すには？', 2, 'JavaScript'),
('JavaScript 基本4', '関数を定義するキーワードは？', 1, 'JavaScript'),
('JavaScript 基本5', 'DOMとは何の略？', 3, 'JavaScript'),
('JavaScript 基本6', 'イベントリスナーを追加する方法は？', 2, 'JavaScript'),
('JavaScript 基本7', 'alert() の機能は？', 0, 'JavaScript'),
('JavaScript 基本8', 'if文の正しい構文は？', 1, 'JavaScript'),
('JavaScript 基本9', 'オブジェクトのプロパティアクセス方法は？', 2, 'JavaScript'),
('JavaScript 基本10', 'for文の目的は？', 3, 'JavaScript');

INSERT INTO choices (quiz_id, choice_text, index_number) VALUES
(21, 'ページ読み込み時のみ動作する', 0),
(21, 'ユーザーの操作やタイマーで動作する', 1),
(21, 'サーバーサイドのみで動作', 2),
(21, 'CSSと一緒に動作', 3),

(22, 'var, let, const を使う', 0),
(22, 'function を使う', 1),
(22, 'int を使う', 2),
(22, 'declare を使う', 3),

(23, 'array.pop()', 0),
(23, 'array.shift()', 1),
(23, 'array[0]', 2),
(23, 'array.slice()', 3),

(24, 'var', 0),
(24, 'function', 1),
(24, 'def', 2),
(24, 'func', 3),

(25, 'Document Object Model', 0),
(25, 'Data Object Model', 1),
(25, 'Document Oriented Model', 2),
(25, 'Data Oriented Model', 3),

(26, 'element.addEventListener()', 0),
(26, 'element.attachEvent()', 1),
(26, 'element.setEvent()', 2),
(26, 'element.onClick()', 3),

(27, '警告ダイアログを表示', 0),
(27, 'コンソールに出力', 1),
(27, 'ページをリロード', 2),
(27, 'フォームを送信', 3),

(28, 'if (condition) { ... }', 0),
(28, 'if condition then', 1),
(28, 'if condition {}', 2),
(28, 'if: condition then', 3),

(29, 'object.property または object["property"]', 0),
(29, 'object->property', 1),
(29, 'object[property]', 2),
(29, 'object.property()', 3),

(30, '繰り返し処理を行う', 0),
(30, '条件分岐を行う', 1),
(30, '関数を呼び出す', 2),
(30, '変数を宣言する', 3);