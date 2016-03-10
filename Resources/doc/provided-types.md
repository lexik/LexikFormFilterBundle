
3. Provided types
=================

The bundle provides form types dedicated to filtering. 
Here the list of these types with their parent type and their specific options.
Of course you can use all options defined by the parent type.

Notes: by default the `required` option is set to `false` for all filter types.

---
**BooleanFilterType:**

Parent type: _boolean_

---
**CheckboxFilterType:**

Parent type: _checkbox_

---
**ChoiceFilterType:**

Parent type: _choice_

---
**DateFilterType:**

Parent type: _date_

---
**DateRangeFilterType:**

This type is composed of two `DateFilterType` types (left_date and right_date).

Parent type: _form_

Options:

* `left_date_options`: options to pass to the left DateFilterType type.
* `right_date_options`: options to pass to the right DateFilterType type.

---
**DateTimeFilterType:**

Parent type: _datetime_

---
**DateTimeRangeFilterType:**

This type is composed of two `DateTimeFilterType` types (left_datetime and right_datetime).

Parent type: _form_

Options:

* `left_datetime_options`: options to pass to the left DateTimeFilterType type.
* `right_datetime_options`: options to pass to the right DateTimeFilterType type.

---
**DocumentFilterType:**

For Doctrine Mongodb only.

Parent type: _document_

Options:

* `reference_type`: reference type of the relation, `one` or `many` (`one` by default).
* `reference_name`: name of the referenced document, by default the type will set this value from the field name.

---
**EntityFilterType:**

For Doctrine ORM only.

Parent type: _entity_

---
**NumberFilterType:**

Parent type: _number_

Options:

* `condition_operator`: this option allows you to configure the operator you want to use, the default operator is FilterOperands::OPERATOR_EQUAL. 
See the FilterOperands::OPERATOR_xxx constants for all available operators (greater than, lower than, ...).
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with the available operators in addition to the input text.

---
**NumberRangeFilterType:**

This type is composed of two `NumberFilterType` types (left_number and right_number).

Parent type: _form_

Options:

* `left_number_options`: options to pass to the left NumberFilterType type.
* `right_number_options`: options to pass to the right NumberFilterType type.

---
**TextFilterType:**

Parent type: _text_

Options:

* `condition_pattern`: this option allows you to configure the way you to filter the string. The default pattern is FilterOperands::STRING_STARTS. 
See the FilterOperands::STRING_xxx constants for all available patterns (starts with, ends with or contains).
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with available patterns in addition to the input text.

***

Next: [4. Example & inner workings](basics.md)
