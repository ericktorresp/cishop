from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Guard(models.Model):
    title = models.CharField(_('title'),max_length=100)
    strength = models.SmallIntegerField(_('strength'),)
    photo = FileBrowseField(_('photo'), max_length=200, directory="guard/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField(_('price'),)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_guard = models.ManyToManyField(User, through='UserGuard')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Guard')
        verbose_name_plural = _('Guards')
        app_label = 'system'
        ordering = ['id', ]

class UserGuard(models.Model):
    user = models.ForeignKey(User)
    guard = models.ForeignKey(Guard)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username + "'s " + self.guard.title
    
    class Meta:
        verbose_name = _('User\'s Guard')
        verbose_name_plural = _('User\'s Guards')
        db_table = 'user_guard'
        app_label = 'system'
