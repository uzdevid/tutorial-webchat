Web chat
==========

Web socket, PHP va javascript orqali yaratilgan kichik onlayn chat

https://youtu.be/wntxLP0Me84

https://youtu.be/uorbI0Wrtgs

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