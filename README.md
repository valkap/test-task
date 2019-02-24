1. При инсталляции модуля создается:
- каталог товаров - инфоблок ("Контент из CSV файла" одноименного типа) с необходимыми свойствами;
- в разделе "Сервисы" в админке пункт меню "Парсер CSV файлов" и соответствующая страница;
- компонент catalog.section в папке /bitix/components/valkap.
2. В админке на странице "Парсер CSV файлов" можно выбрать файл для загрузки стандартным окном Bitrix для выбора файлов и выбрать инфоблок для импорта.
3. При импорте данных по умолчанию задано что первая строка содержит названия полей (если нужно, можно сделать чекбокс для изменения этого параметра)
4. Во время импорта наличие элемента проверяется по полю CODE. При совпадении, элемент обновляется. При обновлении идентичность значений свойств и параметров товара (цена, количество) не проверяется, обновление происходит в любом случае.
5. После импорта выводится количество добавленных и обновленных элементов.
6. Компонент valkap.catalog.section расположен в разделе "Контент" - "Каталог"
7. При деинсталляции удаляются все созданные элементы (инфоблок, страница в админке и пункт меню, компонент).
