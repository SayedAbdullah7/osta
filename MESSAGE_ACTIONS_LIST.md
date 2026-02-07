# قائمة أنواع الرسائل والإجراءات (Message Actions & Types)

## أنواع الرسائل (Message Types)
هذه هي القيم التي تأتي في حقل `message` في الرسائل:

1. **welcome_message**
   - رسالة ترحيبية عند بدء المحادثة
   - تحتوي على إجراء `confirm_order`

2. **pay_order**
   - رسالة دفع الطلب
   - مثال: `pay_order 150` (يحتوي على السعر)

3. **provider_request_additional_cost_equals**
   - طلب مقدم الخدمة لإضافة تكلفة إضافية
   - مثال: `provider_request_additional_cost_equals 50`

4. **provider_added_additional_cost_equals**
   - إشعار بأن مقدم الخدمة أضاف تكلفة إضافية
   - مثال: `provider_added_additional_cost_equals 50`

5. **provider_added_purchases_equals**
   - إشعار بأن مقدم الخدمة أضاف مشتريات
   - مثال: `provider_added_purchases_equals 30`

6. **provider_request_convert_to_offer_equals**
   - طلب مقدم الخدمة لتحويل الطلب إلى عرض
   - مثال: `provider_request_convert_to_offer_equals 200`

7. **provider_request_convert_to_preview**
   - طلب مقدم الخدمة لتحويل الطلب إلى معاينة

8. **provider_converted_the_order_to_preview**
   - إشعار بأن مقدم الخدمة حول الطلب إلى معاينة

9. **user_request_cash_payment**
   - طلب المستخدم للدفع نقداً

10. **invoice**
    - رسالة تحتوي على فاتورة

11. **accepted**
    - رسالة قبول الإجراء

12. **rejected**
    - رسالة رفض الإجراء

13. **cancelled**
    - رسالة إلغاء الطلب

---

## أنواع الإجراءات (Action Names)
هذه هي القيم التي تأتي في حقل `action_name` داخل `info`:

1. **confirm_order**
   - تأكيد الطلب
   - يستخدم مع `welcome_message`

2. **additional_cost**
   - تكلفة إضافية
   - يستخدم مع `provider_request_additional_cost_equals` و `provider_added_additional_cost_equals`

3. **purchases**
   - مشتريات
   - يستخدم مع `provider_added_purchases_equals`

4. **pay**
   - دفع
   - يستخدم مع `pay_order`

5. **cash_payment**
   - دفع نقدي
   - يستخدم مع `user_request_cash_payment`

6. **convert_to_offer**
   - تحويل إلى عرض
   - يستخدم مع `provider_request_convert_to_offer_equals`

7. **convert_to_preview**
   - تحويل إلى معاينة
   - يستخدم مع `provider_request_convert_to_preview` و `provider_converted_the_order_to_preview`

---

## هيكل الرسالة (Message Structure)

```json
{
  "id": 1,
  "content": "welcome_message",
  "sender_id": 123,
  "sender_type": "App\\Models\\Provider",
  "conversation_id": 456,
  "options": {
    "message": "welcome_message",
    "info": {
      "action_name": "confirm_order",
      "action_status": "1",
      "options": [
        {
          "name": "accept",
          "value_response": "1"
        },
        {
          "name": "cancel",
          "value_response": "0"
        }
      ],
      "url": "api/reponse-action",
      "variables": {
        "x": 150
      },
      "description": "وصف اختياري",
      "invoice": {
        // InvoiceResource object
      }
    }
  }
}
```

---

## حقول مهمة في `info`:

- **action_name**: نوع الإجراء (من القائمة أعلاه)
- **action_status**: 
  - `"1"` = الإجراء متاح ويمكن الرد عليه
  - `"0"` = الإجراء غير متاح أو تم إنجازه
- **options**: قائمة الخيارات المتاحة (accept/reject)
- **url**: رابط API للرد على الإجراء (عادة `api/reponse-action`)
- **variables**: متغيرات إضافية (مثل السعر في `x`)
- **description**: وصف اختياري
- **invoice**: بيانات الفاتورة (InvoiceResource) - موجود في بعض الرسائل

---

## ملاحظات للمطور:

1. **الرسائل التي تحتوي على إجراءات قابلة للرد**:
   - يجب التحقق من `action_status == "1"` قبل عرض الأزرار
   - استخدام `url` لإرسال الرد
   - إرسال `value_response` (1 للقبول، 0 للرفض)

2. **الرسائل الإعلامية**:
   - `action_status == "0"` تعني أن الرسالة إعلامية فقط
   - لا تحتاج إلى أزرار رد

3. **المتغيرات**:
   - معظم الرسائل تحتوي على `variables.x` للسعر أو القيمة
   - استخدمها لعرض القيم في الواجهة

4. **الفاتورة**:
   - بعض الرسائل تحتوي على `invoice` object
   - استخدمه لعرض تفاصيل الفاتورة
