from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Guestbook(models.Model):
    owner = models.ForeignKey(User, verbose_name=_('owner'), related_name='owner')
    author = models.ForeignKey(User, verbose_name=_('author'), related_name='author')
    content = models.TextField(_('body'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.author.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('guestbook')
        verbose_name_plural = _('guestbooks')
