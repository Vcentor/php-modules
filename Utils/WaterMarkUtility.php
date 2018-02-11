<?php
/**
 * @name waterMark
 * @desc 给图片添加水印
 * @author xieshuai <xieshuai@yoka.com>
 */

class WaterMark {
	// 图片位置
	const IMG_POSITION_RAND = 0; // 任意位置
	const IMG_POSITION_TOP_LEFT = 1; // 左上角
	const IMG_POSITON_TOP_LEFT_CENTER = 2;// 上方中间
	const IMG_POSITION_TOP_RIGHT = 3; // 右上角
	const IMG_POSITION_CENTER_LEFT = 4; // 中间左边
	const IMG_POSITION_CENTER_CENTER = 5; // 中间位置
	const IMG_POSITION_CENTER_RIGHT = 6; // 中间右边
	const IMG_POSITION_BOTTOM_LEFT = 7; // 左下角
	const IMG_POSITION_BOTTOM_CENTER = 8; // 下方中心
	const IMG_POSITION_BOTTOM_RIGHT = 9; // 右下角
	const WATERMARK_TYPE_IMG = 1; // 图片水印
	const WATERMARK_TYPE_TTF = 2; // 文字水印
	// 原始图片
	public $oriImg;
	// 最终图片
	public $destImg;
	// 添加水印位置
	protected $pos;
	// 图片名称前缀
	public $prefix;
	// 保存路径
	public $path;
	// 水印透明度
	public $pct;

	/**
	 * 初始化
	 * @param string  $dst_img   需要加水印的图片
	 * @param integer $pos       水印的位置
	 * @param string  $prefix    加水印后图片名前缀
	 * @param string  $path      最后图片保存路径，默认和原始图片同一目录
	 * @param integer $pct       水印透明度
	 */
	public function __construct($oriImg, $pos = 9, $prefix = 'water', $path = '', $pct = 80) {
		$this->check($oriImg);
		$this->oriImg = $oriImg;
		$this->pos = $pos;
		$this->prefix = $prefix;
		$this->path = $path;
		$this->pct = $pct;
	}

	/**
	 * 添加图片水印
	 * @param  string $imgWater 图片水印
	 * @return
	 */
	public function water($imgWater) {
		$this->check($imgWater);
		$waterInfo = $this->getImgInfo($imgWater);
		$oriImgInfo = $this->getImgInfo($this->oriImg);
		if ($waterInfo['width'] > $oriImgInfo['width'] || $waterInfo['width'] > $oriImgInfo['height']) {
			throw new \Exception('Too large water!', 90004);
		}
		// 打开图片
		$oriResource = $this->openImg($this->oriImg);
		$waterResource = $this->openImg($imgWater);
		// 获取打水印位置
		$pos = $this->getPos($oriImgInfo, $waterInfo);
		// 打水印
		imagecopymerge($oriResource, $waterResource, $pos['x'], $pos['y'], 0, 0, $waterInfo['width'], $waterInfo['height'], $this->pct);
		// 重新命名
		$this->destImg = $destImgName = $this->makeDestImgName();
		// 保存
		$this->save($oriResource, $destImgName);
		imagedestroy($oriResource);
		imagedestroy($waterResource);
	}

	/**
	 * 获取处理后的图片地址
	 * @return
	 */
	public function getDestImg() {
		return $this->destImg;
	}

	/**
	 * 保存图片
	 * @param  resource $resource   图片资源
	 * @param  string $destImgName  最终图片
	 * @return
	 */
	protected function save($resource, $destImgName) {
		$type = pathinfo($destImgName, PATHINFO_EXTENSION);
		if ($type = 'jpg') {
			$type = 'jpeg';
		}
		$func = 'image'.$type;
		if ( ! is_callable($func)) {
			throw new \Exception('Not callabled function!', 90006);
		}
		$func($resource, $destImgName);
	}

	/**
	 * 生成新的图片名称
	 * @return string
	 */
	protected function makeDestImgName() {
		$baseName = pathinfo($this->oriImg, PATHINFO_BASENAME);
		$dirName = pathinfo($this->oriImg, PATHINFO_DIRNAME);
		$imgName = $this->prefix.'_'.$baseName;
		if ($this->path) {
			$destImgName = rtrim($this->path, '/').'/'.$imgName;
		} else {
			$destImgName = $dirName.'/'.$imgName;
		}
		return $destImgName;
	}

	/**
	 * 获取图片资源
	 * @param  string $img 图片地址
	 * @return resource
	 */
	protected function openImg($img) {
		$mime = $this->getImgInfo($img)['mime'];
		switch ($mime) {
			case 'image/png':
				$resource = imagecreatefrompng($img);
				break;
			case 'image/jpeg':
				$resource = imagecreatefromjpeg($img);
				break;
			case 'image/wbmp':
				$resource = imagecreatefromwbmp($img);
				break;
			case 'image/gif':
				$resource = imagecreatefromgif($img);
				break;
			default:
				throw new \Exception('Not matched img mime!', 90005);
				break;
		}
		return $resource;
	}

	/**
	 * 获取图片信息
	 * @param  string $img 图片路径
	 * @return array
	 */
	protected function getImgInfo($img) {
		$imgInfo = array();
		$result = getimagesize($img);
		$imgInfo['width'] = $result[0];
		$imgInfo['height'] = $result[1];
		$imgInfo['mime'] = $result['mime'];
		return $imgInfo;
	}

	/**
	 * 获取添加水印位置
	 * @param  array $imgInfo   原图片信息
	 * @param  array $waterInfo 水印信息
	 * @return array 位置坐标
	 */
	protected function getPos($imgInfo, $waterInfo) {
		switch ($this->pos) {
			case self::IMG_POSITION_RAND:
				$x = mt_rand(0, $imgInfo['width'] - $waterInfo['width']);
				$y = mt_rand(0, $imgInfo['height'] - $waterInfo['height']);
				break;
		    case self::IMG_POSITION_TOP_LEFT:
				$x = 0;
				$y = 0;
				break;
			case self::IMG_POSITON_TOP_LEFT_CENTER:
				$x = ($imgInfo['width'] - $waterInfo['width']) / 2;
				$y = 0;
				break;
			case self::IMG_POSITION_TOP_RIGHT:
				$x = $imgInfo['width'] - $waterInfo['width'];
				$y = 0;
				break;
			case self::IMG_POSITION_CENTER_LEFT:
				$x = 0;
				$y = ($imgInfo['height'] - $waterInfo['height']) / 2;
				break;
			case self::IMG_POSITION_CENTER_CENTER:
				$x = ($imgInfo['width'] - $waterInfo['width']) / 2;
				$y = ($imgInfo['height'] - $waterInfo['height']) / 2;
				break;
			case self::IMG_POSITION_CENTER_RIGHT:
				$x = $imgInfo['width'] - $waterInfo['width'];
				$y = ($imgInfo['height'] - $waterInfo['height']) / 2;
				break;
			case self::IMG_POSITION_BOTTOM_LEFT:
				$x = 0;
				$y = $imgInfo['height'] - $waterInfo['height'];
				break;
			case self::IMG_POSITION_BOTTOM_CENTER:
				$x = ($imgInfo['width'] - $waterInfo['width']) / 2;
				$y = $imgInfo['height'] - $waterInfo['height'];
				break;
			case self::IMG_POSITION_BOTTOM_RIGHT:
				$x = $imgInfo['width'] - $waterInfo['width'];
				$y = $imgInfo['height'] - $waterInfo['height'];
				break;
			default:
				throw new \Exception('Please choice position of image!', 90003);
				break;
		}

		return array('x' => $x, 'y' => $y);
	}

	/**
	 * 验证图片
	 * @param  string $img 图片路径
	 * @throws object Exception
	 * @return       
	 */
	protected function check($img) {
		if ( ! extension_loaded('gd')) {
			throw new \Exception('Not found GD lib!', 90000);
		}
		if ( ! file_exists($img)) {
			throw new \Exception('File not exit!', 90001);
		}
		$mime = array('.jpg', '.jpeg', '.png', '.gif', '.wbmp');
		$fileExt = strtolower(strrchr($img, '.'));
		if ( ! in_array($fileExt, $mime)) {
			throw new Exception ("Invalid file extension!", 90001);
		}
	}
}

try {
	$oriImg = '1.jpg';
	$water = new waterMark($oriImg);
	$water->water('2.jpg');
} catch (\Exception $e) {
	echo $e->getMessage();
	exit(0);	
}