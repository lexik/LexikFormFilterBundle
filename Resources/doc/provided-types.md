
3. Provided types
=================

The bundle provides form types dedicated to filtering. 
Here the list of these types with their parent type and their specific options.
Of course you can use all options defined by the parent type.

Notes: by default the `required` option is set to `false` for all filter_xxx types.

---
**filter_boolean:**

Parent type: _boolean_

---
**filter_checkbox:**

Parent type: _checkbox_

---
**filter_choice:**

Parent type: _choice_

---
**filter_date:**

Parent type: _date_

---
**filter_date_range:**

This type is composed of two `filter_date` types (left_date and right_date).

Parent type: _form_

Options:

* `left_date_options`: options to pass to the left filter_date type.
* `right_date_options`: options to pass to the right filter_date type.

---
**filter_datetime:**

Parent type: _datetime_

---
**filter_datetime_range:**

This type is composed of two `filter_datetime` types (left_datetime and right_datetime).

Parent type: _form_

Options:

* `left_datetime_options`: options to pass to the left filter_datetime type.
* `right_datetime_options`: options to pass to the right filter_datetime type.

---
**filter_document:**

For Doctrine Mongodb only.

Parent type: _document_

Options:

* `reference_type`: reference type of the relation, `one` or `many` (`one` by default).
* `reference_name`: name of the referenced document, by default the type will set this value from the field name.

---
**filter_entity:**

For Doctrine ORM only.

Parent type: _entity_

**This type does not support many-to-many relations.**

---
**filter_number:**

Parent type: _number_

Options:

* `condition_operator`: this option allows you to configure the operator you want to use, the default operator is FilterOperands::OPERATOR_EQUAL. 
See the FilterOperands::OPERATOR_xxx constants for all available operators (greater than, lower than, ...).
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with the available operators in addition to the input text.

---
**filter_number_range:**

This type is composed of two `filter_number` types (left_number and right_number).

Parent type: _form_

Options:

* `left_number_options`: options to pass to the left filter_number type.
* `right_number_options`: options to pass to the right filter_number type.

---
**filter_text:**

Parent type: _text_

Options:

* `condition_pattern`: this option allows you to configure the way you to filter the string. The default pattern is FilterOperands::STRING_STARTS. 
See the FilterOperands::STRING_xxx constants for all available patterns (starts with, ends with or contains).
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with available patterns in addition to the input text.

***

Next: [4. Example & inner workings](basics.md)
