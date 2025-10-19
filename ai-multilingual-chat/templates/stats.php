<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Статистика AI Chat', 'ai-multilingual-chat'); ?></h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['total_conversations']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9;">
                <?php _e('Всего диалогов', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['active_conversations']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9;">
                <?php _e('Активных диалогов', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 30px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['total_messages']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9;">
                <?php _e('Всего сообщений', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 30px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['unread_messages']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.9;">
                <?php _e('Непрочитанных', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 30px; border-radius: 12px; color: #333; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['today_conversations']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.8;">
                <?php _e('Новых сегодня', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); padding: 30px; border-radius: 12px; color: #333; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $stats['today_messages']; ?>
            </div>
            <div style="font-size: 16px; opacity: 0.8;">
                <?php _e('Сообщений сегодня', 'ai-multilingual-chat'); ?>
            </div>
        </div>
        
    </div>
    
    <?php if (!empty($languages)): ?>
    <div style="background: var(--aic-tab); padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Языки пользователей', 'ai-multilingual-chat'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Язык', 'ai-multilingual-chat'); ?></th>
                    <th><?php _e('Количество диалогов', 'ai-multilingual-chat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languages as $lang): ?>
                <tr>
                    <td><?php echo esc_html($lang->user_language); ?></td>
                    <td><?php echo intval($lang->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($daily_stats)): ?>
    <div style="background: var(--aic-tab); padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Активность за последние 7 дней', 'ai-multilingual-chat'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Дата', 'ai-multilingual-chat'); ?></th>
                    <th><?php _e('Новых диалогов', 'ai-multilingual-chat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_stats as $day): ?>
                <tr>
                    <td><?php echo date('d.m.Y', strtotime($day->date)); ?></td>
                    <td><?php echo intval($day->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
</div>
