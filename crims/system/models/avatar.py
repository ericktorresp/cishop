from django.db import models
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

class Avatar(models.Model):
    filename = FileBrowseField(_('photo'), max_length=200, directory="avatar/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return '<img src=%s border=0 />' % (self.filename)
    __unicode__.allow_tags = True
    
    class Meta:
        verbose_name = _('avatar')
        verbose_name_plural = _('avatars')
        app_label = 'system'
