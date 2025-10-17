import cv2
import os
import sys
#def save_frame_sec():
def save_frame_sec(video_path, sec, result_path):

    print(video_path)
    print(sec)
    print(result_path)

    cap = cv2.VideoCapture(video_path)
    print("1")

    if not cap.isOpened():
        print("hogehogeaer")
        return

    os.makedirs(os.path.dirname(result_path), exist_ok=True)

    print("2")
    #sec = 1

    fps = cap.get(cv2.CAP_PROP_FPS)

    cap.set(cv2.CAP_PROP_POS_FRAMES, round(fps * int(sec)))

    ret, frame = cap.read()

    print("3")

    if ret:
        cv2.imwrite(result_path, frame)
    else:
        print("aaaa")

#save_frame_sec('data/temp/sample_video.mp4', 10, 'data/temp/result_10sec.jpg')
#print(sys.argv[1])
save_frame_sec(sys.argv[1],sys.argv[2],sys.argv[3])
