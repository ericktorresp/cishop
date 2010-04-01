# -*- coding: utf-8 -*-from mysite.lotteries.models import *from django.contrib import adminfrom django import formsfrom django.utils.translation import ugettext_lazy, ugettext as _WEEK_CHOICES = (    (1, u'Monday'),    (2, u'Tuesday'),    (3, u'Wednesday'),    (4, u'Thursday'),    (5, u'Friday'),    (6, u'Saturday'),    (7, u'Sunday'))class LotteryAdminForm(forms.ModelForm):    class Meta:        model = Lottery    def __init__(self, *args, **kwargs):        super(LotteryAdminForm, self).__init__(*args, **kwargs)        self.fields["week_cycle"].widget = forms.CheckboxSelectMultiple(choices=WEEK_CHOICES)        self.fields["week_cycle"].initial = self.instance.week_cycle            def clean_week_cycle(self):        data = eval(self.cleaned_data['week_cycle'])        if data.__len__() == 0:            raise forms.ValidationError('Must choice week day.')        return ','.join(data)class LotteryAdmin(admin.ModelAdmin):    list_display = ('title', 'code')    fieldsets = [        ('General', {'fields': ['title', 'code', 'description', 'lotterytype', 'channel', 'sort', 'issue_rule']}),        ('Date information', {'fields': ['week_cycle', 'yearly_break_start', 'yearly_break_end']}),        ('Settings', {'fields': ['issue_set', 'number_rule']}),        ('Money Setting', {'fields': ['min_commission_gap', 'min_profit']}),    ]    search_fields = ['title']    form = LotteryAdminForm    #    def save_model(self, request, obj, form, change):#        if not obj.id:#            obj.created = datetime.datetime.now()##        obj.week_cycle = sum(map(int, request.POST.getlist('week_cycle')))#        obj.save()class MethodAdmin(admin.ModelAdmin):    list_display = ('title', 'lottery')    fieldsets = [        ('General', {'fields': ['title', 'lottery', 'description', 'is_closed', 'mode', 'parent']}),        ('Prize', {'fields': ['function_name']}),        ('Lock', {'fields': ['is_use_lock', 'init_lock_func', 'lock_table_name']}),        ('Settings', {'fields': ['no_count', 'level_count']}),        ('Money Setting', {'fields': ['max_lost', 'total_price']}),    ]class LockAdmin(admin.ModelAdmin):    actions = ['delete_lock']    def delete_lock(self, request, queryset):        i = 0        for obj in queryset:            from django.db import connection, transaction            cursor = connection.cursor()            cursor.execute("DROP TABLE " + obj.title)            transaction.commit_unless_managed()            obj.delete()            i += 1        if i == 1:            message_bit = "1 lock was"        else:            message_bit = "%s locks were" % i        self.message_user(request, "%s successfully deleted." % message_bit)    delete_lock.short_description = ugettext_lazy("Delete selected %(verbose_name_plural)s") + '(drop table)'    def save_model(self, request, obj, form, change):        if not obj.id:            from django.db import connection, transaction            cursor = connection.cursor()            cursor.execute("CREATE TABLE IF NOT EXISTS `" + obj.title + "` LIKE `lock_example`")            cursor.execute("ALTER TABLE `" + obj.title + "` ADD CONSTRAINT `" + obj.title + "_ibfk` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`)")            cursor.execute("ALTER TABLE `" + obj.title + "` COMMENT=%s", [obj.title])            transaction.commit_unless_managed()        obj.save()    admin.site.register(Lottery, LotteryAdmin)admin.site.register(LotteryType)admin.site.register(Method, MethodAdmin)admin.site.register(Issue)admin.site.register(Mode)admin.site.register(IssueHistory)admin.site.register(IssueError)admin.site.register(PrizeGroup)admin.site.register(PrizeLevel)admin.site.register(Lock, LockAdmin)