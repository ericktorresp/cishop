from record.models import *
from django.contrib import admin

class UserBusinessLogAdmin(admin.ModelAdmin):
    search_fields = ('user__username', 'userbusiness__title')
    list_filter = ('created', 'exited',)

class UserBusinessDailyCountAdmin(admin.ModelAdmin):
    search_fields = ('userbusiness__user__username', 'userbusiness__title')
#    list_filter = ('userbusiness',)

class RespectAdmin(admin.ModelAdmin):
    raw_id_fields = ('user',)
    search_fields = ('user__username',)
    list_filter = ('respect', 'day',)

admin.site.register(Respect, RespectAdmin)
admin.site.register(UserBusinessDailyCount, UserBusinessDailyCountAdmin)
admin.site.register(UserBusinessLog, UserBusinessLogAdmin)
