<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>FAQ - Автоответы</h1>
    
    <p>Настройте автоматические ответы на часто задаваемые вопросы. Система будет автоматически отвечать пользователям, если их сообщение содержит указанные ключевые слова.</p>
    
    <div style="background: var(--aic-tab); padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2>Добавить новый FAQ</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('aic_faq_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="question">Вопрос</label>
                    </th>
                    <td>
                        <input type="text" 
                               name="question" 
                               id="question" 
                               class="regular-text" 
                               required>
                        <p class="description">Пример вопроса для справки</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="answer">Ответ</label>
                    </th>
                    <td>
                        <textarea name="answer" 
                                  id="answer" 
                                  rows="5" 
                                  class="large-text" 
                                  required></textarea>
                        <p class="description">Автоматический ответ, который будет отправлен пользователю</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="keywords">Ключевые слова</label>
                    </th>
                    <td>
                        <input type="text" 
                               name="keywords" 
                               id="keywords" 
                               class="large-text" 
                               required>
                        <p class="description">Ключевые слова через запятую (например: контакты,телефон,связаться)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="language">Язык</label>
                    </th>
                    <td>
                        <select name="language" id="language" class="regular-text">
                            <option value="ru">Русский</option>
                            <option value="en">English</option>
                            <option value="uk">Українська</option>
                            <option value="de">Deutsch</option>
                            <option value="fr">Français</option>
                            <option value="es">Español</option>
                            <option value="it">Italiano</option>
                            <option value="pt">Português</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="aic_add_faq" class="button button-primary" value="Добавить FAQ">
            </p>
        </form>
    </div>
    
    <div style="background: var(--aic-tab); padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2>Существующие FAQ</h2>
        
        <?php if (empty($faqs)): ?>
            <p>Нет созданных FAQ. Добавьте первый!</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 20%;">Вопрос</th>
                        <th style="width: 30%;">Ответ</th>
                        <th style="width: 25%;">Ключевые слова</th>
                        <th style="width: 10%;">Язык</th>
                        <th style="width: 10%;">Статус</th>
                        <th style="width: 5%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><?php echo esc_html($faq->question); ?></td>
                            <td>
                                <?php 
                                $answer = esc_html($faq->answer);
                                echo strlen($answer) > 100 ? substr($answer, 0, 100) . '...' : $answer;
                                ?>
                            </td>
                            <td><?php echo esc_html($faq->keywords); ?></td>
                            <td><?php echo esc_html($faq->language); ?></td>
                            <td>
                                <?php if ($faq->is_active): ?>
                                    <span style="color: green;">✓ Активен</span>
                                <?php else: ?>
                                    <span style="color: red;">✗ Неактивен</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('aic_faq_nonce'); ?>
                                    <input type="hidden" name="faq_id" value="<?php echo $faq->id; ?>">
                                    <button type="submit" name="aic_delete_faq" class="button button-small" 
                                            onclick="return confirm('Удалить этот FAQ?')">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
