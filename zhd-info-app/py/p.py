from pathlib import Path
from pdf2image import convert_from_path
import sys
def pdf_image(pdf_file,img_path,file_name, fmt='jpeg', dpi=200):

    #pdf_file、img_pathをPathにする
    pdf_path = Path(pdf_file)
    image_dir = Path(img_path)

    # PDFをImage に変換(pdf2imageの関数)
    pages = convert_from_path(pdf_path, dpi)

    # 画像ファイルを１ページずつ保存
    for i, page in enumerate(pages):
        #file_name = "{}_{:02d}.{}".format(pdf_path.stem,i+1,fmt)
        image_path = image_dir / file_name
        page.save(image_path, fmt)
        break

if __name__ == "__main__":
    # PDFファイルのパス
    #pdf_path = Path("/home/gyoren-admin/21-040.pdf")
    #img_path=Path("./image")

    #pdf_image(pdf_file=pdf_path,img_path=img_path,file_name, fmt='png', dpi=200)
    pdf_image(sys.argv[1],sys.argv[2],sys.argv[3], fmt='png', dpi=100)
