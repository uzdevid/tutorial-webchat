Web chat
==========

Web socket, PHP va javascript orqali yaratilgan kichik onlayn chat

<iframe width="560" height="315" src="https://www.youtube.com/embed/wntxLP0Me84" title="Web chat" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

<iframe width="560" height="315" src="https://www.youtube.com/embed/uorbI0Wrtgs" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

### O'rnatish

Ushbu loyihani o'rnatishning afzal usuli - [composer](http://getcomposer.org/download/) orqali.

```bash
composer create-project --prefer-dist uzdevid/tutorial-webchat
```

## Foydalanish

#### Ma'lumotlar ombori

Ma'lumotlar omborini yarating, va `config/db.php` fayliga kerakli parametrlarni kiriting.

-----

#### Migratsiyalar

```bash
yii migrate/up
```

--------

#### Worker-ni sozlash

`config/params.php` faylida `workerSocketName` parametriga serverning nomini kiriting.

------

#### Serverni ishga tushirish

```bash
yii chat/run
```