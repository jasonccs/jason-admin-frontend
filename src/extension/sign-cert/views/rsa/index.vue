<template>
  <div class="app-container">
    <el-card>
      <div slot="header" class="clearfix">
        <span>Rsa证书</span>
      </div>

      <el-alert
        type="warning"
        title="注意事项"
        description="生成 rsa 证书需要 php 开启 openssl 扩展"
        style="margin-bottom:15px;"
        show-icon
        :closable="false"
      />

      <div class="filter-container">
        <el-select v-model="setting.len" placeholder="秘钥长度" class="filter-item" style="width: 130px;margin-right: 10px;">
          <el-option v-for="len in lenOptions" :key="len.key" :label="len.display_name" :value="len.key" />
        </el-select>

        <el-select v-model="setting.ktype" placeholder="秘钥格式" class="filter-item" style="width: 130px;margin-right: 10px;">
          <el-option v-for="ktype in ktypeOptions" :key="ktype.key" :label="ktype.display_name" :value="ktype.key" />
        </el-select>

        <el-input v-model="setting.pass" placeholder="秘钥密码，可不填" clearable style="width: 200px;margin-right: 10px;" class="filter-item" />

        <el-button v-waves class="filter-item" type="primary" @click="submit">
          生成证书
        </el-button>
      </div>

      <div class="sign-box">
        <div class="sign-setting-payload">
          <div class="sign-data-tip">
            私钥
            <el-tag type="success" size="mini">
              rsa_private_key.pem
            </el-tag>
            <el-button v-waves size="mini" style="margin-left:10px;" @click="handleClipboard(response.private_key, $event)">
              复制
            </el-button>
          </div>
          <div class="sign-data-input">
            <el-input v-model.trim="response.private_key" type="textarea" rows="8" placeholder="私钥" />
          </div>
        </div>

        <div class="sign-response-data">
          <div class="sign-data-tip">
            公钥
            <el-tag type="success" size="mini">
              rsa_public_key.pem
            </el-tag>
            <el-button v-waves size="mini" style="margin-left:10px;" @click="handleClipboard(response.public_key, $event)">
              复制
            </el-button>
          </div>
          <div class="sign-data-input">
            <el-input v-model.trim="response.public_key" type="textarea" rows="8" placeholder="公钥" />
          </div>
        </div>
      </div>

    </el-card>
  </div>
</template>

<script>
import clipboard from '@/utils/clipboard'
import waves from '@/directive/waves'
import { rsa } from '../../api/signCert'

export default {
  name: 'ConfigIndex',
  components: { },
  directives: { waves },
  filters: {

  },
  data() {
    return {
      setting: {
        len: '2048',
        ktype: 'pkcs8',
        pass: ''
      },
      response: {
        private_key: '',
        public_key: ''
      },
      lenOptions: [
        { key: '512', display_name: '512' },
        { key: '1024', display_name: '1024' },
        { key: '2048', display_name: '2048' },
        { key: '4096', display_name: '4096' }
      ],
      ktypeOptions: [
        { key: 'pkcs1', display_name: 'PKCS#1' },
        { key: 'pkcs8', display_name: 'PKCS#8' }
      ]
    }
  },
  created() {
  },
  methods: {
    handleClipboard(text, event) {
      if (text == '') {
        this.errorTip('请先提交创建后复制')
        return
      }

      clipboard(text, event)
      this.successTip('复制成功')
    },
    submit() {
      this.response.private_key = ''
      this.response.public_key = ''

      rsa(this.setting).then(response => {
        this.response.private_key = response.data.private_key
        this.response.public_key = response.data.public_key

        this.successTip('创建成功')
      })
    }
  }
}
</script>

<style scoped>
.sign-setting-payload {
    margin-bottom: 15px;;
}
.sign-data {
    margin-top: 15px;
}
.sign-data-tip {
    padding: 10px 0;
    font-size: 14px;
    color: #606266;
}
</style>
