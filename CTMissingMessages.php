<?php
/**
 * Handle onMissingTranslation event
 */
class CTMissingMessages extends CApplicationComponent
{
    /**
     * Add missing translations to the source table and
     * If we are using a different translation then the original one
     * Then add the same message to the translation table.
     */
    public static function load($event) {
        // Load the messages
        $source = SourceMessage::model()->find('message=:message AND category=:category', array(':message' => $event->message, ':category' => $event->category));

        // If we didn't find one then add it
        if (!$source) {
            // Add it
            $model = new SourceMessage;

            $model->category = $event->category;
            $model->message = $event->message;
            $model->save();

            $lastID = Yii::app()->db->lastInsertID;
        }

        if ($event->language != Yii::app()->sourceLanguage) {
            $source = SourceMessage::model()->find('message=:message AND category=:category', array(':message' => $event->message, ':category' => $event->category));

            // Do the same thing with the messages
            $translation = Message::model()->find('language=:language AND id=:id', array(':language' => $event->language, ':id' => $source->id));

            // If we didn't find one then add it
            if (!$translation) {
                // Add it
                $model = new Message;

                $model->id = $source->id;
                $model->language = $event->language;
                $model->translation = $event->message;
                $model->save();
            }
        }

    }
}