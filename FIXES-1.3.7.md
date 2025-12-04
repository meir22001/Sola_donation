# תיקונים שבוצעו - גרסה 1.3.7

## 🎯 כל 5 הבעיות תוקנו!

### ✅ בעיה #1: חסרה פונקציית אתחול
**תיקון:** נוספה `initSettingsFromServer()`
- **מיקום:** `form-script.js` שורות 78-102
- **מה היא עושה:**
  1. מסננת כפתורי מטבעות לפי `enabled_currencies`
  2. מגדירה את המטבע ברירת המחדל כפעיל
  3. טוענת סכומים קבועים למטבע ברירת המחדל
  4. מעדכנת סמלים ($, €, וכו')
- **קריאה:** נקראת **ראשונה** ב-`$(document).ready()` - שורה 65

### ✅ בעיה #2: סכומים לא מתעדכנים בשינוי מטבע
**תיקון:** `updateAmountButtons()` (שונתה משם `updatePresetAmounts`)
- **מיקום:** `form-script.js` שורות 338-389
- **מה היא עושה:**
  1. קוראת `formSettings.preset_amounts[currentCurrency]`
  2. מוחקת כפתורי סכום קיימים (מלבד "סכום אחר")
  3. יוצרת כפתורים חדשים עם הסכומים הנכונים
  4. מוסיפה event handlers לכל כפתור
  5. מעדכנת את סכום ברירת המחדל
- **קריאה:** 
  - בטעינה ראשונית (מתוך `initSettingsFromServer`)
  - בשינוי מטבע (מתוך `initCurrencySelection`)

### ✅ בעיה #3: כפתורי מטבע רק למופעלים
**תיקון:** Template מייצר תמיד את כל 4 הכפתורים
- **מיקום:** `form-template.php` שורות 228-252
- **שינוי:**
  - **לפני:** `foreach ($enabled_currencies as $currency)`
  - **אחרי:** `foreach ($all_currencies as $currency => $symbol)`
  - כפתורים מושבתים מקבלים `style="display:none"`
  - JavaScript יכול להציג/להסתיר דינמית
- **יתרון:** אפשרות לעדכון דינמי בעתיד ללא רענון דף

### ✅ בעיה #4: שדות חובה
**סטטוס:** עובד כבר!
- **Template:** `required` attribute מוגדר נכון לפי `$required_fields`
- **JavaScript:** `validateStep()` ו-`validateForm()` משתמשים ב-`formSettings.required_fields`
- **אין צורך בשינוי**

### ✅ בעיה #5: Cache
**תיקון:** עדכון גרסה
- **מיקום:** `sola-donation-plugin.php` שורות 6 ו-21
- **שינוי:** `1.3.6` → `1.3.7`
- **תוצאה:** הדפדפן יוריד את ה-JS החדש

---

## 🔧 פונקציה חדשה: updateEnabledCurrencies()
**מיקום:** `form-script.js` שורות 105-116
- סורקת את כל כפתורי המטבע
- מציגה רק אלו שב-`formSettings.enabled_currencies`
- מסתירה את השאר

---

## 📝 סיכום זרימה:

### טעינת דף:
1. **PHP Template** (`form-template.php`):
   - טוען הגדרות מ-DB
   - מייצר 4 כפתורי מטבע (חלקם מוסתרים)
   - מייצר כפתורי סכומים למטבע ברירת מחדל
   - מגדיר `required` לשדות

2. **JavaScript Init** (`form-script.js`):
   - `initSettingsFromServer()` רצה **ראשון**
   - מסנן מטבעות למופעלים
   - מגדיר מטבע ברירת מחדל
   - יוצר כפתורי סכומים נכונים

### שינוי מטבע:
1. משתמש לוחץ על כפתור מטבע
2. `initCurrencySelection()` handler:
   - מעדכן `currentCurrency`
   - קורא ל-`updateCurrencySymbols()` (משנה $, €, £)
   - קורא ל-`updateAmountButtons()` (יוצר כפתורים חדשים!)

### שליחת טופס:
1. `validateForm()` בודק רק שדות מ-`formSettings.required_fields`
2. שדות תשלום תמיד חובה
3. שליחה ל-API

---

## ✅ לבדיקה:
1. הרץ `force-update.php` כדי לוודא ש-`form_settings` קיים ב-DB
2. נקה cache (Ctrl+Shift+R)
3. כנס לדשבורד → שנה סכומים/מטבעות
4. שמור
5. **בדוק בטופס!**
