from django.db import models
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

class RandomEvent(models.Model):
    title = models.CharField(_('title'), max_length=200)
    photo = FileBrowseField(_('photo'), max_length=200, directory="random/title/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('random event')
        verbose_name_plural = _('random events')
        app_label = 'system'
        ordering = ['id', ]

class RandomEventQuestion(models.Model):
    title = models.CharField(_('title'), max_length=200)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('random event question')
        verbose_name_plural = _('random event questions')
        app_label = 'system'
        ordering = ['id', ]
    
class RandomEventChoice(models.Model):
    randomeventquestion = models.ForeignKey(RandomEventQuestion)
    answer = models.CharField(_('answer'), max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="random/choice/", format="Image", extensions=['.jpg', '.gif', '.png'])
    
    def __unicode__(self):
        return self.answer
    
    class Meta:
        verbose_name = _('random event choice')
        verbose_name_plural = _('random event choices')
        app_label = 'system'
        ordering = ['id', ]
