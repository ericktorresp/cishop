from django.db import models
from django.utils.translation import ugettext_lazy as _

class Game(models.Model):
    display_name = models.CharField(_('Display name'), max_length=100)
    url_name = models.CharField(_('Name for url'), max_length=100)
    url = models.URLField(_('URL'), max_length=255, blank=True, null=True)
    photo = models.ImageField(upload_to='images/games/gamepage', verbose_name=_('photo'), max_length=100)
    add_time = models.DateTimeField(_('Add Time'), auto_now_add=True)
    
    def __unicode__(self):
        return self.display_name
    
    class Meta:
        db_table=u'game'
        verbose_name=_('Game')
        verbose_name_plural=_('Games')