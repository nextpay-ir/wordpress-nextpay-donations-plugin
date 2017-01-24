<p>
    <label for="<?php echo $title_id; ?>">عنوان
    <input class="widefat" id="<?php echo $title_id; ?>" name="<?php echo $title_name; ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </label>
</p>

<p>
    <label for="<?php echo $text_id; ?>">متن
    <textarea class="widefat" id="<?php echo $text_id; ?>" name="<?php echo $text_name; ?>"><?php echo esc_attr($instance['text']); ?></textarea>
    </label>
</p>

<p>
    <label for="<?php echo $amount_id; ?>">مقدار پرداختی
        <input class="widefat" id="<?php echo $amount_id; ?>" name="<?php echo $amount_name; ?>" type="number"><?php echo esc_attr($instance['amount']); ?></input>
    </label>
</p>

<p>
    <label for="<?php echo $name_id; ?>">درخواست نام
        <input class="checkbox" type="checkbox" <?php checked( $instance[ 'name' ], 'on' ); ?> id="<?php echo $name_id; ?>" name="<?php echo $name_name; ?>"/>
    </label>
</p>

<p>
    <label for="<?php echo $email_id; ?>">درخواست ایمیل
        <input class="checkbox" type="checkbox" <?php checked( $instance[ 'email' ], 'on' ); ?> id="<?php echo $email_id; ?>" name="<?php echo $email_name; ?>"/>
    </label>
</p>

<p>
    <label for="<?php echo $desc_id; ?>">درخواست توضیحات
        <input class="checkbox" type="checkbox" <?php checked( $instance[ 'desc' ], 'on' ); ?> id="<?php echo $desc_id; ?>" name="<?php echo $desc_name; ?>"/>
    </label>
</p>