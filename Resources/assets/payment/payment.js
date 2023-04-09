/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

/* кнопка Добавить коллекцию */

let $addButtonField = document.getElementById('field_addCollection');

if ($addButtonField)
{
    /* Блок для новой коллекции */
    let $blockCollection = document.getElementById('field_collection');

    if ($blockCollection) {

        /* Добавляем новую коллекцию */
        $addButtonField.addEventListener('click', function ()
        {
            /* получаем прототип коллекции  */
            let newForm = this.dataset.prototype;
            let index = this.dataset.index * 1;


            /* Замена '__name__' в HTML-коде прототипа на
            вместо этого будет число, основанное на том, сколько коллекций */
            newForm = newForm.replace(/__payment_field__/g, index);

            newForm = newForm.replace(/__FIELD_SORT__/g, index * 100 + 100);

            //newForm = newForm.replace(/__FIELD_SORT__/g, 100);
            //newForm = newForm.replace(/__FIELDS_INDEX__/g, 1);


            /* Вставляем новую коллекцию */
            let div = document.createElement('div');
            div.id = 'item_payment_form_field_' + index;
            div.classList.add('mb-3');


            div.innerHTML = newForm;
            $blockCollection.append(div);


            /* Плавная прокрутка к элементу */
            div.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});

            (div.querySelector('.del-item-field'))?.addEventListener('click', deleteField);

            // let field = document.getElementById('field-collection-' + index);
            // field.innerHTML = field.innerHTML.replace(/__FIELDS__/g, '0')
            //     .replace(/__FIELD_SORT__/g, '100');


            /* Добавить поле в секцию */
            //createSectionField(index);


            /* Удаляем при клике СЕКЦИЮ */
            //deleteSection(div);
            /* Удаляем при клике СВОЙСТВО */
            //deleteField(div);



            /* Увеличиваем data-index на 1 после вставки новой коллекции */
            this.dataset.index = (index + 1).toString();

        });


    }

}


document.querySelectorAll('.del-item-field').forEach(function (item) {
    item.addEventListener('click', deleteField);
});

function deleteField() {
    document.getElementById(this.dataset.delete).remove();
}
